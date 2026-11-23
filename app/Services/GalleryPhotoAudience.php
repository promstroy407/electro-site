<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Audience;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Collection;

/**
 * Для кого фото: «для дома» показываются в разделе «Для дома»,
 * «для бизнеса» — на главной. Меняется у нескольких фото сразу.
 */
final readonly class GalleryPhotoAudience
{
    /**
     * @param  Collection<int, GalleryPhoto>  $photos
     * @return int сколько фото изменено
     */
    public function assign(Collection $photos, Audience $audience): int
    {
        $ids = $photos->modelKeys();

        GalleryPhoto::query()->whereKey($ids)->update(['audience' => $audience->value]);

        return count($ids);
    }
}
