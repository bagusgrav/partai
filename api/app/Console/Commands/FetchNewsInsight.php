<?php

namespace App\Console\Commands;

use App\Models\GovernmentInsight;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class FetchNewsInsight extends Command
{
    protected $signature = 'fetch:news-insight';

    protected $description = 'Fetch recent news from the public ANTARA RSS feed';

    public function handle(): int
    {
        $url = 'https://www.antaranews.com/rss/terkini.xml';

        try {
            $response = Http::accept('application/rss+xml, application/xml, text/xml')
                ->connectTimeout(5)
                ->timeout(20)
                ->retry([200, 500], throw: false)
                ->get($url);
        } catch (ConnectionException $exception) {
            $this->error('Unable to connect to the ANTARA RSS feed: '.$exception->getMessage());

            return self::FAILURE;
        }

        if (! $response->successful()) {
            $this->error("The ANTARA RSS feed failed with HTTP status {$response->status()}.");

            return self::FAILURE;
        }

        $previousLibxmlSetting = libxml_use_internal_errors(true);
        $feed = simplexml_load_string($response->body(), SimpleXMLElement::class, LIBXML_NONET | LIBXML_NOCDATA);
        libxml_clear_errors();
        libxml_use_internal_errors($previousLibxmlSetting);

        if (! $feed instanceof SimpleXMLElement || ! isset($feed->channel->item)) {
            $this->error('The ANTARA RSS feed returned invalid XML.');

            return self::FAILURE;
        }

        $items = [];

        foreach ($feed->channel->item as $item) {
            $title = trim((string) $item->title);
            $sourceUrl = $this->safeHttpUrl(trim((string) $item->link));

            if ($title === '' || $sourceUrl === null) {
                continue;
            }

            $description = html_entity_decode(strip_tags((string) $item->description), ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $publishedAt = strtotime((string) $item->pubDate);
            $enclosureUrl = $this->safeHttpUrl(trim((string) ($item->enclosure['url'] ?? '')));

            $items[] = [
                'title' => $title,
                'content' => trim($description),
                'source_url' => $sourceUrl,
                'image_url' => $enclosureUrl,
                'published_at' => $publishedAt === false ? now() : date('Y-m-d H:i:s', $publishedAt),
            ];
        }

        usort(
            $items,
            fn (array $first, array $second): int => strtotime((string) $second['published_at']) <=> strtotime((string) $first['published_at']),
        );
        $items = array_slice($items, 0, 10);

        if ($items === []) {
            $this->error('The ANTARA RSS feed contained no usable news items.');

            return self::FAILURE;
        }

        foreach ($items as $item) {
            GovernmentInsight::query()->updateOrCreate(
                ['category' => 'berita', 'source_url' => $item['source_url']],
                $item,
            );
        }

        $this->info(sprintf('Saved %d recent news items from ANTARA.', count($items)));

        return self::SUCCESS;
    }

    private function safeHttpUrl(string $url): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        return in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) ? $url : null;
    }
}
