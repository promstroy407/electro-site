<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Еженедельная копия базы владельцу на почту. Вложение уже зашифровано
 * на сервере (deploy/offsite-backup.sh), письмо его только доставляет.
 */
final class DatabaseBackupMail extends Mailable
{
    public function __construct(
        public readonly string $fileName,
        public readonly string $contents,
        public readonly string $companyName,
        public readonly string $date,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Копия базы сайта '.$this->companyName.' за '.$this->date);
    }

    public function content(): Content
    {
        return new Content(
            text: 'mail.database-backup',
            with: ['fileName' => $this->fileName, 'date' => $this->date],
        );
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn (): string => $this->contents, $this->fileName)
                ->withMime('application/octet-stream'),
        ];
    }
}
