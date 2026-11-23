<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\NotificationSettings;

/**
 * Единственная запись настроек уведомлений. Миграция создаёт её сразу,
 * здесь — страховка на случай, если строку удалили руками.
 */
final class NotificationSettingsStore
{
    public function current(): NotificationSettings
    {
        return NotificationSettings::query()->orderBy('id')->first()
            ?? NotificationSettings::query()->create([
                'emails' => [],
                'mail_enabled' => true,
                'telegram_enabled' => false,
            ]);
    }
}
