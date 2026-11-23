<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\MediaKind;
use App\Models\GalleryPhoto;
use App\Services\GalleryPhotos;

/**
 * Вид (фото или видео) по файлу; не оставлять на диске файлы, которые
 * заменили или удалили вместе с записью.
 */
final readonly class GalleryPhotoObserver
{
    public function __construct(
        private GalleryPhotos $files,
    ) {}

    /** Фото это или видео — решает расширение файла, а не ручной выбор. */
    public function saving(GalleryPhoto $photo): void
    {
        $photo->kind = MediaKind::fromPath($photo->path);
    }

    public function updated(GalleryPhoto $photo): void
    {
        foreach (['path', 'poster_path'] as $field) {
            $before = $photo->getOriginal($field);

            if ($photo->wasChanged($field) && is_string($before)) {
                $this->files->delete($before);
            }
        }
    }

    public function deleted(GalleryPhoto $photo): void
    {
        $this->files->delete($photo->path);

        if ($photo->poster_path !== null) {
            $this->files->delete($photo->poster_path);
        }
    }
}
