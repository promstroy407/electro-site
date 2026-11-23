<?php

declare(strict_types=1);

namespace App\Mail;

use App\Services\LeadNotifications\LeadMessage;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Письмо о заявке: простой текст, тот же, что уходит в мессенджеры. */
final class LeadNotificationMail extends Mailable
{
    public function __construct(
        public readonly LeadMessage $leadMessage,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->leadMessage->subject);
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.lead-notification',
            with: ['body' => $this->leadMessage->body],
        );
    }
}
