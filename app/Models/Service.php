<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Audience;
use App\Enums\ServiceIcon;
use Carbon\CarbonImmutable;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Услуга или направление услуг (если parent_id = null).
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $title
 * @property string $slug
 * @property string|null $summary
 * @property string|null $body
 * @property Audience $audience
 * @property ServiceIcon|null $icon
 * @property int $sort_order
 * @property bool $is_published
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Service|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Service> $children
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PriceItem> $priceItems
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Project> $projects
 */
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'parent_id',
        'title',
        'slug',
        'summary',
        'body',
        'audience',
        'icon',
        'sort_order',
        'is_published',
        'meta_title',
        'meta_description',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'audience' => 'all',
        'sort_order' => 0,
        'is_published' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'audience' => Audience::class,
            'icon' => ServiceIcon::class,
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<Service, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')
            ->orderBy('sort_order')
            ->orderBy('title');
    }

    /**
     * @return HasMany<PriceItem, $this>
     */
    public function priceItems(): HasMany
    {
        return $this->hasMany(PriceItem::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * @return BelongsToMany<Project, $this>
     */
    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class);
    }

    /** Иконка для сайта: своя, иначе направления, иначе общая. */
    public function iconOrDefault(): ServiceIcon
    {
        return $this->icon ?? ServiceIcon::General;
    }

    public function isDirection(): bool
    {
        return $this->parent_id === null;
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeDirections(Builder $query): void
    {
        $query->whereNull('parent_id');
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeForAudience(Builder $query, Audience $audience): void
    {
        $query->whereIn('audience', $audience->matchingValues());
    }

    /**
     * @param  Builder<Service>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('title');
    }
}
