<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Audience;
use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GalleryPhoto>
 */
class GalleryPhotoFactory extends Factory
{
    protected $model = GalleryPhoto::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'path' => 'gallery/'.fake()->unique()->numberBetween(1, 999999).'.jpg',
            'caption' => rtrim(fake()->sentence(5), '.'),
            'category' => fake()->randomElement(PhotoCategory::cases()),
            'place' => null,
            'audience' => Audience::Business,
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    public function forAudience(Audience $audience): static
    {
        return $this->state(['audience' => $audience]);
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}
