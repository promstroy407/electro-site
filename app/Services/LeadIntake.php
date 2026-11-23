<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadStatus;
use App\Jobs\ExportLeadToTenderRadar;
use App\Jobs\SendLeadNotifications;
use App\Models\Lead;
use App\Services\TenderRadar\TenderRadarExporter;
use Illuminate\Contracts\Bus\Dispatcher;

/**
 * Приём заявки: сохранить и поставить в очередь уведомления и передачу
 * в воронку «Тендер-Радара».
 * Посетитель не ждёт отправки писем и сообщений, а сбой мессенджера
 * не теряет заявку — она уже в базе.
 */
final readonly class LeadIntake
{
    public function __construct(
        private PhoneNormalizer $phones,
        private Dispatcher $bus,
        private Calculator $calculator,
        private TenderRadarExporter $tenderRadar,
    ) {}

    public function accept(LeadSubmission $submission): Lead
    {
        // Снимок ставок на момент отправки: потом их могут поменять в админке.
        $estimate = $submission->estimateAreaM2 !== null
            ? $this->calculator->estimate($submission->estimateAreaM2, $submission->estimateRateIds)
            : null;

        $lead = Lead::query()->create([
            'service_id' => $submission->serviceId,
            'name' => $submission->name,
            'phone' => $this->phones->normalize($submission->phone),
            'email' => $submission->email,
            'organization' => $submission->organization,
            'facility' => $submission->facility,
            'message' => $submission->message,
            'status' => LeadStatus::New,
            'source_url' => $submission->sourceUrl,
            'origin' => $submission->origin,
            'estimate_area_m2' => $estimate?->areaM2,
            'estimate_items' => $estimate?->itemsForStorage(),
            'consent_at' => now(),
        ]);

        $this->bus->dispatch(new SendLeadNotifications($lead->id));

        if ($this->tenderRadar->isEnabled()) {
            $this->bus->dispatch(new ExportLeadToTenderRadar($lead->id));
        }

        return $lead;
    }
}
