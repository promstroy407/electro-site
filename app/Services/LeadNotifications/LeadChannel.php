<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use App\Models\NotificationSettings;
use Throwable;

/**
 * Канал уведомлений о заявках. Новый канал (MAX, SMS) — новый класс
 * с этим интерфейсом и строка в LeadNotifier::channels().
 */
interface LeadChannel
{
    /** Название для людей: «Почта», «Telegram». */
    public function label(): string;

    public function isEnabled(NotificationSettings $settings): bool;

    /**
     * @throws Throwable при любой ошибке отправки — её поймает LeadNotifier
     */
    public function send(NotificationSettings $settings, LeadMessage $message): void;
}
