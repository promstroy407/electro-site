<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\NotificationSettingsStore;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Seeder;

/**
 * Начальная почта для заявок из SITE_NOTIFY_EMAIL. Ничего не трогает,
 * если адреса уже заданы в админке.
 */
final class NotificationSettingsSeeder extends Seeder
{
    public function run(NotificationSettingsStore $store, Config $config): void
    {
        $settings = $store->current();
        $email = $config->get('site.notify_email');

        if ($settings->emails !== [] || ! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $settings->update(['emails' => [$email], 'mail_enabled' => true]);
        $this->command?->info('Заявки будут приходить на '.$email.' (меняется в админке: Настройки → Уведомления).');
    }
}
