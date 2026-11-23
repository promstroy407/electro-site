<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\AdminAccounts;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory as Validation;

/**
 * Первый администратор на боевом сервере, где демо-данные не заливаются:
 * `php artisan admin:create owner@example.ru`. Пароль печатается один раз.
 */
final class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {email : Почта для входа в админку}
        {--name=Администратор : Имя в админке}';

    protected $description = 'Создать администратора со случайным паролем';

    public function __construct(
        private readonly AdminAccounts $accounts,
        private readonly Validation $validation,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = [
            'email' => (string) $this->argument('email'),
            'name' => (string) $this->option('name'),
        ];

        $validator = $this->validation->make($input, [
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->accounts->create($input['email'], $input['name']);

        $this->info('Администратор создан: '.$input['email']);
        $this->warn('Пароль: '.$password);
        $this->warn('Пароль показан один раз — сохраните его и смените после входа.');

        return self::SUCCESS;
    }
}
