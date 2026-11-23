<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\AdminAccounts;
use Illuminate\Database\Seeder;

/**
 * Демо-администратор для локальной разработки. Пароль случайный,
 * печатается один раз при создании и нигде не хранится в открытом виде.
 */
final class AdminUserSeeder extends Seeder
{
    public const EMAIL = 'admin@example.test';

    public function run(AdminAccounts $accounts): void
    {
        if ($accounts->exists(self::EMAIL)) {
            $this->command?->info('Демо-администратор уже есть: '.self::EMAIL.' (пароль прежний).');

            return;
        }

        $password = $accounts->create(self::EMAIL, 'Администратор');

        $this->command?->warn('Вход в админку: '.self::EMAIL.' / '.$password);
        $this->command?->warn('Пароль показан один раз — сохраните его.');
    }
}
