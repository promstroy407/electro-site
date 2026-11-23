<?php

declare(strict_types=1);

namespace App\Enums;

/** Что лежит в галерее: фото или видео. Определяется по расширению файла. */
enum MediaKind: string
{
    case Photo = 'photo';
    case Video = 'video';

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Фото',
            self::Video => 'Видео',
        };
    }

    public static function fromPath(string $path): self
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'mov'], true) ? self::Video : self::Photo;
    }
}
