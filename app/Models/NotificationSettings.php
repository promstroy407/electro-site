<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;

/**
 * Куда отправлять уведомления о заявках. Одна запись на сайт,
 * получать через App\Services\NotificationSettingsStore.
 *
 * @property int $id
 * @property list<string> $emails
 * @property bool $mail_enabled
 * @property bool $telegram_enabled
 * @property string|null $telegram_chat_id
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
class NotificationSettings extends Model
{
    protected $table = 'notification_settings';

    /** @var list<string> */
    protected $fillable = [
        'emails',
        'mail_enabled',
        'telegram_enabled',
        'telegram_chat_id',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'emails' => '[]',
        'mail_enabled' => true,
        'telegram_enabled' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'emails' => 'array',
            'mail_enabled' => 'boolean',
            'telegram_enabled' => 'boolean',
        ];
    }
}
