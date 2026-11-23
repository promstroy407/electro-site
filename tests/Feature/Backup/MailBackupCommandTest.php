<?php

declare(strict_types=1);

use App\Mail\DatabaseBackupMail;
use App\Services\BackupMailer;
use Illuminate\Support\Facades\Mail;

function backupFile(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'backup');
    file_put_contents($path, $contents);

    return $path;
}

beforeEach(function (): void {
    Mail::fake();
});

it('отправляет копию вложением на указанную почту', function (): void {
    $path = backupFile('зашифрованные байты');

    $this->artisan('backup:mail', [
        'path' => $path,
        '--to' => 'owner@example.test',
        '--name' => 'db-2026-11-08.sql.gz.enc',
    ])->assertSuccessful();

    Mail::assertSent(DatabaseBackupMail::class, function (DatabaseBackupMail $mail): bool {
        return $mail->hasTo('owner@example.test')
            && $mail->fileName === 'db-2026-11-08.sql.gz.enc'
            && $mail->contents === 'зашифрованные байты'
            && count($mail->attachments()) === 1
            && str_contains($mail->envelope()->subject ?? '', (string) config('site.company_name'));
    });
});

it('письмо объясняет, что это и зачем хранить', function (): void {
    $text = (new DatabaseBackupMail('db.enc', 'x', 'Компания', '08.11.2026'))->render();

    expect($text)->toContain('08.11.2026')
        ->toContain('db.enc')
        ->toContain('не пересылайте');
});

it('не отправляет пустую копию', function (): void {
    $this->artisan('backup:mail', [
        'path' => backupFile(''),
        '--to' => 'owner@example.test',
        '--name' => 'db.enc',
    ])->assertFailed();

    Mail::assertNothingSent();
});

it('не отправляет копию больше лимита вложения', function (): void {
    $this->artisan('backup:mail', [
        'path' => backupFile(str_repeat('x', BackupMailer::MAX_BYTES + 1)),
        '--to' => 'owner@example.test',
        '--name' => 'db.enc',
    ])->assertFailed();

    Mail::assertNothingSent();
});

it('отклоняет неверную почту, имя вложения и несуществующий файл', function (array $options, string $path): void {
    $this->artisan('backup:mail', ['path' => $path, ...$options])->assertFailed();

    Mail::assertNothingSent();
})->with([
    'не почта' => [['--to' => 'owner', '--name' => 'db.enc'], __FILE__],
    'без почты' => [['--name' => 'db.enc'], __FILE__],
    'имя с путём' => [['--to' => 'owner@example.test', '--name' => '../db.enc'], __FILE__],
    'имя длиннее 100' => [['--to' => 'owner@example.test', '--name' => str_repeat('a', 101)], __FILE__],
    'нет файла' => [['--to' => 'owner@example.test', '--name' => 'db.enc'], '/nonexistent/db.enc'],
]);
