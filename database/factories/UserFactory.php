<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'role' => Role::Manager,
            'is_active' => true,
            'email_verified_at' => now(),
            // Случайный пароль на каждую запись: известного пароля в коде нет.
            'password' => Str::password(16),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => Role::Admin]);
    }

    public function editor(): static
    {
        return $this->state(['role' => Role::Editor]);
    }

    public function manager(): static
    {
        return $this->state(['role' => Role::Manager]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
