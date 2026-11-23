<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Логотипы партнёров: папка partners на диске public.
 */
final readonly class PartnerLogos
{
    public const DISK = PublicFiles::DISK;

    public const DIRECTORY = 'partners';

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
