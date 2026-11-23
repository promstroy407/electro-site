<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\CalculatorRateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Ставка калькулятора «Предварительный расчёт»: цена работ за 1 м².
 *
 * @property int $id
 * @property string $title
 * @property int $rate_kopecks
 * @property bool $is_default
 * @property int $sort_order
 * @property bool $is_published
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class CalculatorRate extends Model
{
    /** @use HasFactory<CalculatorRateFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'title',
        'rate_kopecks',
        'is_default',
        'sort_order',
        'is_published',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_default' => false,
        'sort_order' => 0,
        'is_published' => true,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rate_kopecks' => 'integer',
            'is_default' => 'boolean',
            'sort_order' => 'integer',
            'is_published' => 'boolean',
        ];
    }
}
