<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Audience;
use App\Enums\MediaKind;
use App\Enums\PhotoCategory;
use App\Observers\GalleryPhotoObserver;
use Carbon\CarbonImmutable;
use Database\Factories\GalleryPhotoFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Фото или видео с объекта для галереи.
 *
 * @property int $id
 * @property string $path
 * @property MediaKind $kind
 * @property string|null $poster_path
 * @property string $caption
 * @property PhotoCategory $category
 * @property string|null $place
 * @property Audience $audience
 * @property bool $is_published
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[ObservedBy(GalleryPhotoObserver::class)]
class GalleryPhoto extends Model
{
    /** @use HasFactory<GalleryPhotoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'path',
        'kind',
        'poster_path',
        'caption',
        'category',
        'place',
        'audience',
        'is_published',
        'sort_order',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'kind' => 'photo',
        'audience' => 'business',
        'is_published' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'kind' => MediaKind::class,
            'category' => PhotoCategory::class,
            'audience' => Audience::class,
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function isVideo(): bool
    {
        return $this->kind === MediaKind::Video;
    }

    /** Картинка для превью: само фото или обложка видео (если есть). */
    public function previewPath(): ?string
    {
        return $this->isVideo() ? $this->poster_path : $this->path;
    }

    /**
     * @param  Builder<GalleryPhoto>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * Фото для посетителя из этой аудитории: свои и общие.
     *
     * @param  Builder<GalleryPhoto>  $query
     */
    public function scopeFor(Builder $query, Audience $audience): void
    {
        $query->whereIn('audience', $audience->matchingValues());
    }

    /**
     * @param  Builder<GalleryPhoto>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
