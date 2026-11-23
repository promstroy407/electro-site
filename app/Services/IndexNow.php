<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Http\Client\Factory as Http;
use RuntimeException;

/**
 * IndexNow: сообщает Яндексу о страницах сайта, которых он от нас ещё
 * не получал. Список страниц — тот же, что в sitemap.xml; отправленные
 * адреса запоминаются в indexnow_submissions.
 */
final readonly class IndexNow
{
    /** Ограничение протокола на один запрос. */
    private const BATCH = 10_000;

    public function __construct(
        private Http $http,
        private Config $config,
        private ConnectionInterface $db,
        private Sitemap $sitemap,
    ) {}

    /** Ключ из .env, если он годится по правилам протокола; иначе null. */
    public function key(): ?string
    {
        $key = $this->config->get('services.indexnow.key');

        return is_string($key) && preg_match('/^[A-Za-z0-9-]{8,128}$/', $key) === 1 ? $key : null;
    }

    /**
     * Страницы из sitemap, о которых Яндекс от нас ещё не слышал.
     *
     * @return list<string>
     */
    public function pending(): array
    {
        $urls = $this->sitemap->urls();
        /** @var list<string> $sent */
        $sent = $this->db->table('indexnow_submissions')->whereIn('url', $urls)->pluck('url')->all();

        return array_values(array_diff($urls, $sent));
    }

    /**
     * Отправляет новые страницы и запоминает их. Возвращает, сколько
     * адресов отправлено. При отказе Яндекса ничего не запоминает —
     * адреса уйдут при следующем обновлении сайта.
     *
     * @throws RuntimeException ключ не задан или Яндекс не принял запрос
     */
    public function submitPending(): int
    {
        $key = $this->key() ?? throw new RuntimeException('Ключ IndexNow не задан (SITE_INDEXNOW_KEY).');
        $pending = $this->pending();

        foreach (array_chunk($pending, self::BATCH) as $batch) {
            $host = (string) parse_url($batch[0], PHP_URL_HOST);

            $response = $this->http->timeout(30)->acceptJson()->post($this->endpoint(), [
                'host' => $host,
                'key' => $key,
                'urlList' => $batch,
            ]);

            // 200 — принято, 202 — ключ ещё проверяется: адреса Яндекс
            // всё равно получил, повторно их слать не нужно.
            if (! in_array($response->status(), [200, 202], true)) {
                throw new RuntimeException(sprintf('Яндекс не принял адреса (код %d).', $response->status()));
            }

            $now = now();
            $this->db->table('indexnow_submissions')->insertOrIgnore(
                array_map(fn (string $url): array => ['url' => $url, 'submitted_at' => $now], $batch),
            );
        }

        return count($pending);
    }

    private function endpoint(): string
    {
        $endpoint = $this->config->get('services.indexnow.endpoint');

        return is_string($endpoint) ? $endpoint : '';
    }
}
