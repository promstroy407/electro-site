<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BackupMailer;
use Illuminate\Console\Command;
use Illuminate\Contracts\Validation\Factory as Validation;
use InvalidArgumentException;
use Throwable;

/**
 * Отправка зашифрованной копии базы на почту. Вызывает
 * deploy/offsite-backup.sh раз в неделю, копию подаёт на вход:
 * `php artisan backup:mail - --to=почта --name=db-2026-11-08.sql.gz.enc`.
 */
final class MailBackup extends Command
{
    protected $signature = 'backup:mail
        {path : Файл копии, «-» — со стандартного входа}
        {--to= : Почта, куда отправить}
        {--name= : Имя вложения}';

    protected $description = 'Отправить копию базы на почту владельца';

    public function __construct(
        private readonly BackupMailer $mailer,
        private readonly Validation $validation,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $input = [
            'to' => (string) $this->option('to'),
            'name' => (string) $this->option('name'),
        ];

        $validator = $this->validation->make($input, [
            'to' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z0-9][a-z0-9._-]*$/'],
        ], [
            'name.regex' => 'Имя вложения — латиница, цифры, точка, дефис.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // «-» — читаем уже открытый вход. /dev/stdin не годится: в контейнере
        // он открывается заново, а канал принадлежит root, и www-data
        // получает отказ в доступе.
        $path = (string) $this->argument('path');
        $contents = @file_get_contents($path === '-' ? 'php://stdin' : $path);

        if ($contents === false) {
            $this->error('Не удалось прочитать копию.');

            return self::FAILURE;
        }

        try {
            $this->mailer->send($input['to'], $input['name'], $contents, now()->format('d.m.Y'));
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (Throwable $exception) {
            $this->error('Почта не отправилась: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info(sprintf('Копия отправлена на %s (%d КБ).', $input['to'], intdiv(strlen($contents) + 1023, 1024)));

        return self::SUCCESS;
    }
}
