<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PriceUnit;
use Carbon\CarbonImmutable;
use Database\Factories\PriceItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Позиция прайса.
 *
 * @property int $id
 * @property int $service_id
 * @property string $title
 * @property PriceUnit $unit
 * @property int $price_kopecks
 * @property bool $is_from
 * @property int $sort_order
 * @property bool $is_published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Service $service
 */
class PriceItem extends Model
{
    /** @use HasFactory<PriceItemFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service_id',
        'title',
        'unit',
        'price_kopecks',
        'is_from',
        'sort_order',
        'is_published',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_from' => true,
        'sort_order' => 0,
        'is_published' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit' => PriceUnit::class,
            'price_kopecks' => 'integer',
            'is_from' => 'boolean',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
