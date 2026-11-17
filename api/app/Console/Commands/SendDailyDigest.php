<?php

namespace App\Console\Commands;

use App\Models\GovernmentInsight;
use App\Models\User;
use App\Models\WelfareData;
use App\Notifications\DailyDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SendDailyDigest extends Command
{
    protected $signature = 'send:daily-digest';

    protected $description = 'Send members a daily quiz, government news, and welfare data digest';

    public function handle(): int
    {
        $news = GovernmentInsight::query()
            ->whereIn('category', ['berita', 'news', 'ANTARA'])
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->first();

        $welfare = WelfareData::query()->inRandomOrder()->first();

        if ($news === null || $welfare === null || ! $this->quizItems()->exists()) {
            $this->error('Daily digest needs at least one quiz, news item, and welfare data record.');

            return self::FAILURE;
        }

        $newsData = [
            'title' => $news->title,
            'content' => $news->content,
            'source_url' => $news->source_url,
            'published_at' => $news->published_at?->toISOString(),
        ];
        $welfareData = [
            'region_name' => $welfare->region_name,
            'indicator' => $welfare->indicator,
            'value' => (float) $welfare->value,
            'unit' => $welfare->unit,
            'year' => (int) $welfare->year,
            'source' => $welfare->source,
        ];

        $sent = 0;
        User::query()
            ->where('role', 'member')
            ->chunkById(100, function ($users) use ($newsData, $welfareData, &$sent): void {
                foreach ($users as $user) {
                    $user->notify(new DailyDigestNotification([
                        'quiz' => $this->quizFor($user),
                        'news' => $newsData,
                        'welfare' => $welfareData,
                    ]));
                    $sent++;
                }
            });

        $this->info("Sent daily digests to {$sent} members.");

        return self::SUCCESS;
    }

    private function quizFor(User $user): array
    {
        $quiz = $this->quizItems()
            ->whereNotExists(function (Builder $query) use ($user): void {
                $query->selectRaw('1')
                    ->from('reviews')
                    ->whereColumn('reviews.item_id', 'items.id')
                    ->where('reviews.user_id', $user->getKey());
            })
            ->inRandomOrder()
            ->first();

        $quiz ??= $this->quizItems()->inRandomOrder()->first();
        if ($quiz === null) {
            throw new RuntimeException('No quiz item is available for the daily digest.');
        }

        $payload = json_decode($quiz->payload, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($payload) || ! is_string($payload['question'] ?? null)) {
            throw new RuntimeException("Quiz item [{$quiz->id}] has no valid question.");
        }

        return [
            'id' => $quiz->id,
            'question' => $payload['question'],
            'domain' => $quiz->domain,
            'level' => $quiz->level,
        ];
    }

    private function quizItems(): Builder
    {
        return DB::table('items')
            ->where(function (Builder $query): void {
                $query->whereRaw("json_extract(payload, '$.type') IS NULL")
                    ->orWhereRaw("json_extract(payload, '$.type') != ?", ['essay']);
            });
    }
}
