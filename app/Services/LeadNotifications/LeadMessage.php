<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

/** Текст уведомления, одинаковый для всех каналов. */
final readonly class LeadMessage
{
    public function __construct(
        public string $subject,
        public string $body,
    ) {}
}
