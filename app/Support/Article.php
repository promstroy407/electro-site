<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Статья из resources/content/stati: шапка «ключ: значение» между
 * строками --- и текст в Markdown.
 */
final readonly class Article
{
    /** Иллюстрации — компоненты resources/views/components/illustrations/stati. */
    public const ILLUSTRATIONS = ['shchit', 'kabel', 'pozhar', 'kamera', 'skud', 'sks'];

    /** Тег статей для компаний: в разделе «Для дома» их не показываем. */
    public const BUSINESS_TAG = 'Для компаний';

    private const REQUIRED = ['title', 'tag', 'illustration', 'seo_title', 'seo_description', 'excerpt', 'order'];

    public function __construct(
        public string $slug,
        public string $title,
        public string $tag,
        public string $illustration,
        public string $seoTitle,
        public string $seoDescription,
        public string $keywords,
        public string $excerpt,
        public int $order,
        public string $body,
    ) {}

    public static function fromFile(string $slug, string $contents): self
    {
        if (! preg_match('/\A---\n(.*?)\n---\n(.*)\z/s', str_replace("\r\n", "\n", $contents), $parts)) {
            throw new InvalidArgumentException("Статья {$slug}: нет шапки между строками ---");
        }

        $meta = [];
        foreach (explode("\n", $parts[1]) as $line) {
            [$key, $value] = array_pad(explode(':', $line, 2), 2, '');
            $meta[trim($key)] = trim($value);
        }

        foreach (self::REQUIRED as $key) {
            if (($meta[$key] ?? '') === '') {
                throw new InvalidArgumentException("Статья {$slug}: не заполнено поле {$key}");
            }
        }

        if (! in_array($meta['illustration'], self::ILLUSTRATIONS, true)) {
            throw new InvalidArgumentException("Статья {$slug}: нет иллюстрации {$meta['illustration']}");
        }

        return new self(
            slug: $slug,
            title: $meta['title'],
            tag: $meta['tag'],
            illustration: $meta['illustration'],
            seoTitle: $meta['seo_title'],
            seoDescription: $meta['seo_description'],
            keywords: $meta['keywords'] ?? '',
            excerpt: $meta['excerpt'],
            order: (int) $meta['order'],
            body: trim($parts[2]),
        );
    }

    public function isForBusiness(): bool
    {
        return $this->tag === self::BUSINESS_TAG;
    }

    /** Минуты чтения: 180 слов в минуту, не меньше одной. */
    public function readingMinutes(): int
    {
        $words = preg_match_all('/[\p{L}\p{N}]+/u', $this->body);

        return max(1, (int) round($words / 180));
    }
}
