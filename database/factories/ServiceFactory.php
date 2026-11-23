<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Audience;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->unique()->sentence(3), '.');

        return [
            'parent_id' => null,
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'summary' => fake()->sentence(12),
            'body' => fake()->paragraphs(2, true),
            'audience' => Audience::All,
            'sort_order' => fake()->numberBetween(0, 100),
            'is_published' => true,
            'meta_title' => null,
            'meta_description' => null,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function childOf(Service $parent): static
    {
        return $this->state(['parent_id' => $parent->id]);
    }

    public function forAudience(Audience $audience): static
    {
        return $this->state(['audience' => $audience]);
    }
}
