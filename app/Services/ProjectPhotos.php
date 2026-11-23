<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Фото объектов портфолио: папка portfolio на диске public.
 */
final readonly class ProjectPhotos
{
    public const DISK = PublicFiles::DISK;

    public const DIRECTORY = 'portfolio';

    public function __construct(
        private PublicFiles $files,
    ) {}

    public function url(string $path): string
    {
        return $this->files->url($path);
    }

    /**
     * @param  list<string>  $paths
     */
    public function delete(array $paths): void
    {
        $this->files->deleteWithin(self::DIRECTORY, $paths);
    }
}
