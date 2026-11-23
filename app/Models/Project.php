<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\ProjectObserver;
use Carbon\CarbonImmutable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Выполненный объект для портфолио.
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $city
 * @property int $year
 * @property string|null $summary
 * @property string|null $body
 * @property list<string> $photos
 * @property bool $is_published
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Service> $services
 */
#[ObservedBy(ProjectObserver::class)]
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'slug',
        'city',
        'year',
        'summary',
        'body',
        'photos',
        'is_published',
        'sort_order',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'photos' => '[]',
        'is_published' => false,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'photos' => 'array',
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->orderBy('sort_order')->orderBy('title');
    }

    /** Обложка — первое фото. */
    public function coverPath(): ?string
    {
        return $this->photos[0] ?? null;
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Project>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderByDesc('year')->orderBy('sort_order')->orderByDesc('id');
    }
}
