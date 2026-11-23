<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CalculatorRate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CalculatorRate>
 */
class CalculatorRateFactory extends Factory
{
    protected $model = CalculatorRate::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => rtrim(fake()->sentence(2), '.'),
            'rate_kopecks' => fake()->numberBetween(1, 30) * 10_000,
            'is_default' => false,
            'sort_order' => 0,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
