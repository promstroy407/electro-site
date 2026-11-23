<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Collection;

/**
 * Фотогалерея для публичной части: только опубликованные фото.
 */
final readonly class GalleryCatalog
{
    /**
     * Без аудитории — все фото (страница «Фото объектов»), с аудиторией —
     * её фото и общие (главная — компании, раздел «Для дома» — частники).
     *
     * @return Collection<int, GalleryPhoto>
     */
    public function photos(?int $limit = null, ?Audience $audience = null): Collection
    {
        $query = GalleryPhoto::query()->published()->ordered();

        if ($audience !== null) {
            $query->for($audience);
        }

        if ($limit !== null) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Разделы, в которых есть фото, с их количеством — для фильтра.
     *
     * @param  Collection<int, GalleryPhoto>  $photos
     * @return list<array{category: PhotoCategory, count: int}>
     */
    public function categories(Collection $photos): array
    {
        $result = [];
        foreach (PhotoCategory::cases() as $category) {
            $count = $photos->filter(fn (GalleryPhoto $photo): bool => $photo->category === $category)->count();
            if ($count > 0) {
                $result[] = ['category' => $category, 'count' => $count];
            }
        }

        return $result;
    }

    /**
     * Объекты, где снято больше одного фото, — второй ряд фильтра.
     *
     * @param  Collection<int, GalleryPhoto>  $photos
     * @return array<string, int> подпись объекта => количество фото
     */
    public function places(Collection $photos): array
    {
        $counts = [];
        foreach ($photos as $photo) {
            if ($photo->place !== null && $photo->place !== '') {
                $counts[$photo->place] = ($counts[$photo->place] ?? 0) + 1;
            }
        }

        return array_filter($counts, static fn (int $count): bool => $count > 1);
    }

    /**
     * Карточки объектов для главной: обложка — первое фото объекта,
     * под ним — разделы, в которых на объекте есть фото.
     *
     * @param  Collection<int, GalleryPhoto>  $photos
     * @return list<array{place: string, count: int, cover: GalleryPhoto, categories: list<PhotoCategory>}>
     */
    public function placeCards(Collection $photos, int $limit = 3): array
    {
        $cards = [];
        foreach ($this->places($photos) as $place => $count) {
            $own = $photos->filter(fn (GalleryPhoto $photo): bool => $photo->place === $place)->values();
            $stills = $own->reject(fn (GalleryPhoto $photo): bool => $photo->isVideo());
            $categories = [];
            foreach ($own as $photo) {
                if (! in_array($photo->category, $categories, true)) {
                    $categories[] = $photo->category;
                }
            }

            $cover = $stills->first();
            if ($cover === null) {
                continue;
            }

            $cards[] = ['place' => (string) $place, 'count' => $count, 'cover' => $cover, 'categories' => $categories];
        }

        usort($cards, static fn (array $a, array $b): int => $b['count'] <=> $a['count']);

        return array_slice($cards, 0, $limit);
    }

    /**
     * Фото для блока на главной: только снимки, без видео.
     *
     * @param  Collection<int, GalleryPhoto>  $photos
     * @return Collection<int, GalleryPhoto>
     */
    public function stills(Collection $photos, int $limit): Collection
    {
        return $photos->reject(fn (GalleryPhoto $photo): bool => $photo->isVideo())->take($limit)->values();
    }
}
