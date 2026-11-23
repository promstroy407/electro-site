<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Lead>
 */
class LeadFactory extends Factory
{
    protected $model = Lead::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'service_id' => null,
            'name' => fake()->name(),
            'phone' => '+79'.fake()->numerify('#########'),
            'email' => fake()->boolean() ? fake()->safeEmail() : null,
            'organization' => null,
            'facility' => null,
            'message' => fake()->sentence(10),
            'status' => LeadStatus::New,
            'manager_note' => null,
            'source_url' => null,
            'origin' => null,
            'consent_at' => now(),
        ];
    }

    public function forService(Service $service): static
    {
        return $this->state(['service_id' => $service->id]);
    }

    public function withStatus(LeadStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
