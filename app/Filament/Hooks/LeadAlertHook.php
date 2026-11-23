<?php

declare(strict_types=1);

namespace App\Filament\Hooks;

use App\Models\Lead;
use App\Services\LeadPulse;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\View\Factory as Views;

/**
 * Звук и всплывающее сообщение о новой заявке в админке. Подключается
 * в конец каждой страницы панели, только тем, кто видит заявки.
 */
final readonly class LeadAlertHook
{
    public function __construct(
        private Gate $gate,
        private LeadPulse $pulse,
        private Views $views,
    ) {}

    public function __invoke(): string
    {
        if ($this->gate->denies('viewAny', Lead::class)) {
            return '';
        }

        return $this->views->make('filament.lead-alert', [
            'latestId' => $this->pulse->latestId(),
        ])->render();
    }
}
