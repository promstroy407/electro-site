<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Учётки администраторов, которые заводятся не из админки: первый вход
 * на боевом сервере и демо-администратор. Пароль случайный, его
 * возвращают один раз — показать человеку и нигде не сохранять.
 */
final class AdminAccounts
{
    public function exists(string $email): bool
    {
        return User::query()->where('email', $email)->exists();
    }

    /**
     * @return string пароль в открытом виде, в базе только его хеш
     */
    public function create(string $email, string $name): string
    {
        $password = Str::password(16, symbols: false);

        User::query()->create([
            'name' => $name,
            'email' => $email,
            'role' => Role::Admin,
            'is_active' => true,
            'password' => $password,
        ]);

        return $password;
    }
}
