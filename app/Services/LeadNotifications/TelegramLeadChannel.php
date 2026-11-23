<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use App\Models\NotificationSettings;

final readonly class TelegramLeadChannel implements LeadChannel
{
    public function __construct(
        private TelegramBot $bot,
    ) {}

    public function label(): string
    {
        return 'Telegram';
    }

    public function isEnabled(NotificationSettings $settings): bool
    {
        return $settings->telegram_enabled
            && $settings->telegram_chat_id !== null
            && $settings->telegram_chat_id !== ''
            && $this->bot->hasToken();
    }

    public function send(NotificationSettings $settings, LeadMessage $message): void
    {
        $this->bot->sendMessage((string) $settings->telegram_chat_id, $message->body);
    }
}
