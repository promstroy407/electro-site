<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\LeadNotifications\LeadNotifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Уведомления о новой заявке. Одна попытка: каналы сами ловят свои
 * ошибки, а повтор задвоил бы уже доставленные сообщения.
 */
final class SendLeadNotifications implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public readonly int $leadId,
    ) {}

    public function handle(LeadNotifier $notifier): void
    {
        $lead = Lead::query()->with('service')->find($this->leadId);

        // Заявку могли удалить, пока задача ждала в очереди.
        if ($lead === null) {
            return;
        }

        $notifier->notifyAboutLead($lead);
    }
}
