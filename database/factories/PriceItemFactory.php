<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PriceUnit;
use App\Models\PriceItem;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PriceItem>
 */
class PriceItemFactory extends Factory
{
    protected $model = PriceItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => Service::factory(),
            'title' => rtrim(fake()->sentence(3), '.'),
            'unit' => PriceUnit::Piece,
            'price_kopecks' => fake()->numberBetween(100, 5000) * 100,
            'is_from' => true,
            'sort_order' => 0,
            'is_published' => true,
        ];
    }

    public function forService(Service $service): static
    {
        return $this->state(['service_id' => $service->id]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
