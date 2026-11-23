<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;
use Illuminate\Database\ConnectionInterface;

/**
 * Загрузка в галерею сразу нескольких фото с одного объекта: раздел и
 * объект общие, подпись берётся из имени файла («03 Кабельная эстакада.jpg»
 * → «Кабельная эстакада»). Порядок — по номеру в начале имени.
 */
final readonly class GalleryBulkUpload
{
    /** Подпись, если в имени файла нет слов — только номер или «IMG_1234». */
    public const DEFAULT_CAPTION = 'Фото с объекта';

    public const CAPTION_MAX = 200;

    public function __construct(
        private ConnectionInterface $db,
    ) {}

    /**
     * @param  list<string>  $paths  сохранённые файлы на диске галереи
     * @param  array<string, string>  $originalNames  путь → исходное имя файла
     * @return int сколько фото добавлено
     */
    public function store(
        array $paths,
        array $originalNames,
        PhotoCategory $category,
        ?string $place,
        bool $published,
        Audience $audience = Audience::Business,
    ): int {
        $place = $place !== null && trim($place) !== '' ? trim($place) : null;

        $this->db->transaction(function () use ($paths, $originalNames, $category, $place, $published, $audience): void {
            foreach ($paths as $path) {
                $name = $originalNames[$path] ?? basename($path);

                GalleryPhoto::query()->create([
                    'path' => $path,
                    'caption' => $this->captionFromFileName($name),
                    'category' => $category,
                    'place' => $place,
                    'audience' => $audience,
                    'is_published' => $published,
                    'sort_order' => $this->orderFromFileName($name),
                ]);
            }
        });

        return count($paths);
    }

    public function captionFromFileName(string $fileName): string
    {
        $caption = pathinfo($fileName, PATHINFO_FILENAME);
        // Номер в начале («03 », «03_», «03-», «03.») и служебные имена камер.
        $caption = (string) preg_replace('/^\s*\d+[\s._-]*/u', '', $caption);
        $caption = (string) preg_replace('/^(IMG|DSC|DSCN|PXL|VID)[_-]?\d*$/iu', '', $caption);
        $caption = trim((string) preg_replace('/[\s_]+/u', ' ', $caption));

        if ($caption === '') {
            return self::DEFAULT_CAPTION;
        }

        return mb_substr(mb_strtoupper(mb_substr($caption, 0, 1)).mb_substr($caption, 1), 0, self::CAPTION_MAX);
    }

    /** Номер в начале имени — порядок на сайте (колонка smallint). */
    public function orderFromFileName(string $fileName): int
    {
        if (preg_match('/^\s*(\d{1,4})(?!\d)/u', pathinfo($fileName, PATHINFO_FILENAME), $match) === 1) {
            return min((int) $match[1], 32767);
        }

        return 0;
    }
}
