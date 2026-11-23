<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Файлы фотогалереи (фото, видео, обложки видео): папка gallery на диске public.
 */
final readonly class GalleryPhotos
{
    public const DISK = PublicFiles::DISK;

    public const DIRECTORY = 'gallery';

    public function __construct(
        private PublicFiles $files,
    ) {}

    public function url(string $path): string
    {
        return $this->files->url($path);
    }

    public function delete(string $path): void
    {
        $this->files->deleteWithin(self::DIRECTORY, [$path]);
    }
}
