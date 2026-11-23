<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = rtrim(fake()->sentence(3), '.');

        return [
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'city' => fake()->city(),
            'year' => fake()->numberBetween(2018, 2026),
            'summary' => fake()->sentence(10),
            'body' => fake()->paragraphs(2, true),
            'photos' => [],
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    /**
     * @param  list<string>  $paths
     */
    public function withPhotos(array $paths): static
    {
        return $this->state(['photos' => $paths]);
    }
}
