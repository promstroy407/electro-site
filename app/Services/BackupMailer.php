<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\DatabaseBackupMail;
use App\Support\SiteInfo;
use Illuminate\Contracts\Mail\Mailer;
use InvalidArgumentException;

/**
 * Отправляет зашифрованную копию базы на почту владельца. Шифрует и
 * собирает копию скрипт на сервере, здесь — только проверка и письмо,
 * чтобы использовать те же настройки SMTP, что и уведомления о заявках.
 */
final class BackupMailer
{
    /** Лимит вложения у почтовых сервисов около 25–30 МБ — берём с запасом. */
    public const MAX_BYTES = 20 * 1024 * 1024;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly SiteInfo $site,
    ) {}

    public function send(string $to, string $fileName, string $contents, string $date): void
    {
        if ($contents === '') {
            throw new InvalidArgumentException('Копия пустая — письмо не отправлено.');
        }

        if (strlen($contents) > self::MAX_BYTES) {
            throw new InvalidArgumentException('Копия больше 20 МБ и не пройдёт по почте.');
        }

        $this->mailer->to($to)->send(
            new DatabaseBackupMail($fileName, $contents, $this->site->companyName, $date),
        );
    }
}
