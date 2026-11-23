<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Lead;
use App\Services\TenderRadar\TenderRadarExporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Заявка → воронка «Тендер-Радара». В отличие от уведомлений, здесь можно
 * повторять: «Тендер-Радар» не создаёт дубль по тому же external_id.
 * Заявка в любом случае уже в базе сайта, на почте и в Telegram.
 */
final class ExportLeadToTenderRadar implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [60, 300];

    public function __construct(
        public readonly int $leadId,
    ) {}

    public function handle(TenderRadarExporter $exporter): void
    {
        $lead = Lead::query()->with('service')->find($this->leadId);

        if ($lead === null || ! $exporter->isEnabled()) {
            return;
        }

        $exporter->export($lead);
    }

    public function failed(Throwable $e): void
    {
        app(LoggerInterface::class)->error('Заявка не передана в Тендер-Радар', [
            'lead' => $this->leadId,
            'error' => $e->getMessage(),
        ]);
    }
}
