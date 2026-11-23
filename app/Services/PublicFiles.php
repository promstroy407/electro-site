<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Contracts\Filesystem\Factory as Filesystems;
use Illuminate\Contracts\Filesystem\Filesystem;

/**
 * Загруженные картинки на диске public: адрес для сайта и удаление
 * файлов, которые больше не нужны. Удаляет только внутри своей папки.
 */
final readonly class PublicFiles
{
    public const DISK = 'public';

    public function __construct(
        private Filesystems $filesystems,
    ) {}

    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    /**
     * @param  list<string>  $paths
     */
    public function deleteWithin(string $directory, array $paths): void
    {
        $own = array_values(array_filter(
            $paths,
            static fn (string $path): bool => str_starts_with($path, $directory.'/') && ! str_contains($path, '..'),
        ));

        if ($own !== []) {
            $this->disk()->delete($own);
        }
    }

    private function disk(): Filesystem
    {
        return $this->filesystems->disk(self::DISK);
    }
}
