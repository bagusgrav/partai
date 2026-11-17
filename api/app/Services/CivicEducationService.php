<?php

namespace App\Services;

use App\Models\CivicAnswerRequest;
use App\Models\CivicQuestion;
use App\Models\Event;
use App\Models\FactionContract;
use App\Models\MemberReputation;
use App\Models\User;
use App\Models\UserQuestionSchedule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class CivicEducationService
{
    public function __construct(private readonly ReputationService $reputation) {}

    /**
     * @return array<string, mixed>
     */
    public function overview(): array
    {
        $published = CivicQuestion::query()->where('review_status', 'published');
        $counts = (clone $published)
            ->selectRaw('domain, mastery_level, COUNT(*) as total')
            ->groupBy('domain', 'mastery_level')
            ->get()
            ->groupBy('domain');

        $domains = [];
        foreach (ReputationService::DOMAINS as $domain) {
            $levels = [];
            for ($level = 1; $level <= 4; $level++) {
                $levels[$level] = (int) ($counts->get($domain)?->firstWhere('mastery_level', $level)?->total ?? 0);
            }
            $domains[$domain] = [
                'count' => array_sum($levels),
                'levels' => $levels,
            ];
        }

        $questions = (clone $published)->orderBy('id')->get();
        $featured = $questions->isEmpty()
            ? null
            : $questions->get(now()->dayOfYear % $questions->count());
        $samples = (clone $published)->inRandomOrder()->limit(3)->get();

        return [
            'curriculum' => collect(ReputationService::DOMAINS)->map(fn (string $domain): array => [
                'domain' => $domain,
                'label' => $this->domainLabel($domain),
                'levels' => CivicQuestion::MASTERY_LEVELS,
            ])->all(),
            'distribution' => $domains,
            'daily_digest' => [
                'date' => now()->toDateString(),
                'featured_question' => $featured === null ? null : $this->publicQuestion($featured),
                'context' => [
                    'active_faction_contracts' => FactionContract::query()
                        ->where('status', 'SEDANG_BERJALAN')
                        ->count(),
                    'upcoming_civic_agendas' => Event::query()
                        ->where('status', 'active')
                        ->where('starts_at', '>=', now())
                        ->orderBy('starts_at')
                        ->limit(3)
                        ->get(['id', 'title', 'starts_at'])
                        ->map(fn (Event $event): array => [
                            'id' => $event->id,
                            'title' => $event->title,
                            'starts_at' => $event->starts_at?->toISOString(),
                        ])->all(),
                ],
            ],
            'preview_questions' => $samples->map(fn (CivicQuestion $question): array => $this->publicQuestion($question))->all(),
        ];
    }

    /**
     * @return array{questions: array<int, array<string, mixed>>, mastery_scores: array<string, int>, overall_mastery_level: int}
     */
    public function session(User $user): array
    {
        $profile = MemberReputation::query()->firstOrCreate(
            ['user_id' => $user->id],
            ['mastery_scores' => $this->reputation->emptyMasteryScores()],
        );
        $due = UserQuestionSchedule::query()
            ->with('question')
            ->where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->whereHas('question', fn ($query) => $query->where('review_status', 'published'))
            ->orderBy('next_review_at')
            ->limit(5)
            ->get();
        $questions = $due->map(fn (UserQuestionSchedule $schedule): array => [
            ...$this->publicQuestion($schedule->question),
            'is_due' => true,
            'next_review_at' => $schedule->next_review_at?->toISOString(),
        ])->all();

        if (count($questions) < 5) {
            $seenIds = UserQuestionSchedule::query()
                ->where('user_id', $user->id)
                ->pluck('civic_question_id');
            $level = max(1, min(4, (int) $profile->overall_mastery_level));
            $newQuestions = CivicQuestion::query()
                ->where('review_status', 'published')
                ->whereNotIn('id', $seenIds)
                ->orderByRaw('ABS(mastery_level - ?)', [$level])
                ->orderBy('mastery_level')
                ->limit(5 - count($questions))
                ->get();
            foreach ($newQuestions as $question) {
                $questions[] = [
                    ...$this->publicQuestion($question),
                    'is_due' => false,
                    'next_review_at' => null,
                ];
            }
        }

        return [
            'questions' => $questions,
            'mastery_scores' => $profile->mastery_scores ?? $this->reputation->emptyMasteryScores(),
            'overall_mastery_level' => (int) $profile->overall_mastery_level,
        ];
    }

    /**
     * @param  array{request_id: string, question_id: string, selected_option: string, confidence: ?string}  $input
     * @return array{response: array<string, mixed>, replayed: bool}
     */
    public function answer(User $user, array $input): array
    {
        $payloadHash = hash('sha256', json_encode([
            'question_id' => $input['question_id'],
            'selected_option' => $input['selected_option'],
            'confidence' => $input['confidence'],
        ], JSON_THROW_ON_ERROR));

        try {
            return DB::transaction(function () use ($user, $input, $payloadHash): array {
                $existingRequest = CivicAnswerRequest::query()
                    ->where('request_id', $input['request_id'])
                    ->first();

                if ($existingRequest !== null) {
                    return $this->replay($existingRequest, $user, $payloadHash);
                }

                $question = CivicQuestion::query()
                    ->where('review_status', 'published')
                    ->findOrFail($input['question_id']);
                $correct = $input['selected_option'] === $question->correct_option;
                $member = $user->role === 'member';
                $pointsDelta = 0;
                $masteryAwarded = false;
                $nextReviewAt = null;
                $profile = $member
                    ? MemberReputation::query()->firstOrCreate(
                        ['user_id' => $user->id],
                        ['mastery_scores' => $this->reputation->emptyMasteryScores()],
                    )
                    : null;

                if ($member) {
                    $schedule = UserQuestionSchedule::query()->firstOrCreate(
                        ['user_id' => $user->id, 'civic_question_id' => $question->id],
                        ['next_review_at' => now()],
                    );
                    $schedule = UserQuestionSchedule::query()->whereKey($schedule->id)->lockForUpdate()->firstOrFail();
                    $grade = $correct ? ($input['confidence'] === 'yakin' ? 5 : 4) : 1;
                    $this->updateSchedule($schedule, $grade);

                    if ($correct && ! $schedule->mastery_awarded) {
                        $ledger = $this->reputation->record([
                            'user_id' => $user->id,
                            'score_type' => 'mastery',
                            'domain' => $question->domain,
                            'source_type' => 'civic_question',
                            'source_id' => $question->id,
                            'delta' => $question->mastery_points,
                            'reason' => "Penguasaan materi ({$question->domain} Lv.{$question->mastery_level}): {$question->source_citation}",
                            'request_id' => $input['request_id'],
                        ]);
                        $masteryAwarded = $ledger['created'];
                        $pointsDelta = $masteryAwarded ? $question->mastery_points : 0;
                        $schedule->forceFill(['mastery_awarded' => true])->save();
                    }

                    $nextReviewAt = $schedule->next_review_at;
                    $profile = MemberReputation::query()->where('user_id', $user->id)->first();
                }

                $response = [
                    'correct' => $correct,
                    'correct_option' => $question->correct_option,
                    'explanation' => $question->explanation,
                    'source_citation' => $question->source_citation,
                    'source_url' => $question->source_url,
                    'mastery_awarded' => $masteryAwarded,
                    'points_delta' => $pointsDelta,
                    'next_review_at' => $nextReviewAt?->toISOString(),
                    'updated_reputation' => $profile === null ? null : [
                        'mastery_scores' => $profile->mastery_scores,
                        'overall_mastery_level' => (int) $profile->overall_mastery_level,
                    ],
                ];
                CivicAnswerRequest::query()->create([
                    'user_id' => $user->id,
                    'request_id' => $input['request_id'],
                    'payload_hash' => $payloadHash,
                    'response' => $response,
                ]);

                return ['response' => $response, 'replayed' => false];
            }, 3);
        } catch (QueryException $exception) {
            $existingRequest = CivicAnswerRequest::query()
                ->where('request_id', $input['request_id'])
                ->first();

            if ($existingRequest === null) {
                throw $exception;
            }

            return $this->replay($existingRequest, $user, $payloadHash);
        }
    }

    /**
     * @return array{response: array<string, mixed>, replayed: bool}
     */
    private function replay(CivicAnswerRequest $request, User $user, string $payloadHash): array
    {
        if ($request->user_id !== $user->id || ! hash_equals($request->payload_hash, $payloadHash)) {
            throw new ConflictHttpException('request_id sudah digunakan untuk jawaban yang berbeda.');
        }

        return ['response' => $request->response, 'replayed' => true];
    }

    private function updateSchedule(UserQuestionSchedule $schedule, int $grade): void
    {
        $easeFactor = (float) $schedule->ease_factor;
        if ($grade < 3) {
            $schedule->forceFill([
                'repetitions' => 0,
                'interval_days' => 1,
                'ease_factor' => max(1.30, $easeFactor + (0.1 - (5 - $grade) * (0.08 + (5 - $grade) * 0.02))),
                'last_reviewed_at' => now(),
                'next_review_at' => now()->addDay(),
            ])->save();

            return;
        }

        $interval = match ((int) $schedule->repetitions) {
            0 => 1,
            1 => 6,
            default => (int) round((int) $schedule->interval_days * $easeFactor),
        };
        $schedule->forceFill([
            'repetitions' => $schedule->repetitions + 1,
            'interval_days' => $interval,
            'ease_factor' => max(1.30, $easeFactor + (0.1 - (5 - $grade) * (0.08 + (5 - $grade) * 0.02))),
            'last_reviewed_at' => now(),
            'next_review_at' => now()->addDays($interval),
        ])->save();
    }

    /**
     * @return array<string, mixed>
     */
    private function publicQuestion(CivicQuestion $question): array
    {
        return [
            'id' => $question->id,
            'domain' => $question->domain,
            'mastery_level' => $question->mastery_level,
            'prompt' => $question->prompt,
            'options' => $question->options,
            'source_citation' => $question->source_citation,
            'source_url' => $question->source_url,
        ];
    }

    private function domainLabel(string $domain): string
    {
        return [
            'hak_konstitusional' => 'Hak Konstitusional',
            'alasan_bernegara' => 'Alasan Bernegara',
            'keuangan_negara' => 'Keuangan Negara',
            'hukum' => 'Hukum',
            'politik_bernegara' => 'Politik Bernegara',
        ][$domain] ?? $domain;
    }
}
