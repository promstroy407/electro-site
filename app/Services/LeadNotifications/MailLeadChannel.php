<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use App\Mail\LeadNotificationMail;
use App\Models\NotificationSettings;
use Illuminate\Contracts\Mail\Factory as MailFactory;

final readonly class MailLeadChannel implements LeadChannel
{
    public function __construct(
        private MailFactory $mail,
    ) {}

    public function label(): string
    {
        return 'Почта';
    }

    public function isEnabled(NotificationSettings $settings): bool
    {
        return $settings->mail_enabled && $settings->emails !== [];
    }

    public function send(NotificationSettings $settings, LeadMessage $message): void
    {
        $this->mail->mailer()
            ->to($settings->emails)
            ->send(new LeadNotificationMail($message));
    }
}
