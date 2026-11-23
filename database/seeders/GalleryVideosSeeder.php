<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;
use App\Services\GalleryPhotos;
use Illuminate\Contracts\Filesystem\Factory as Filesystems;
use Illuminate\Database\Seeder;

/**
 * Видео со сдачи объектов от владельца (ноябрь 2026), со звуком.
 * Сжаты для сайта (H.264, 720p), к каждому — обложка-кадр.
 * Заливается и на проде (через ContentSeeder), если видео в галерее ещё нет.
 */
final class GalleryVideosSeeder extends Seeder
{
    private const SOURCE = __DIR__.'/gallery-videos';

    public function run(Filesystems $filesystems): void
    {
        $disk = $filesystems->disk(GalleryPhotos::DISK);

        foreach (['sdacha-obekta-1', 'sdacha-obekta-2'] as $index => $name) {
            foreach (['mp4', 'jpg'] as $extension) {
                $path = GalleryPhotos::DIRECTORY.'/'.$name.'.'.$extension;
                if (! $disk->exists($path)) {
                    $disk->put($path, (string) file_get_contents(self::SOURCE.'/'.$name.'.'.$extension));
                }
            }

            GalleryPhoto::query()->firstOrCreate(['path' => GalleryPhotos::DIRECTORY.'/'.$name.'.mp4'], [
                'poster_path' => GalleryPhotos::DIRECTORY.'/'.$name.'.jpg',
                'caption' => 'Сдача объекта',
                'category' => PhotoCategory::Fire,
                'place' => null,
                'is_published' => true,
                'sort_order' => 1000 + $index * 10,
            ]);
        }
    }
}
