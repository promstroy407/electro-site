<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Services\LeadIntake;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Routing\UrlGenerator;

final readonly class LeadController
{
    public function __construct(
        private LeadIntake $intake,
        private Redirector $redirector,
        private UrlGenerator $urls,
    ) {}

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        $this->intake->accept($request->submission());
        $estimate = $request->isEstimate();

        // lead_sent — для цели Метрики, lead_form — какой форме показать «Спасибо».
        return $this->redirector
            ->to($this->urls->previous().($estimate ? '#raschet' : '#zayavka'))
            ->with('lead_sent', true)
            ->with('lead_form', $estimate ? 'estimate' : 'contact');
    }
}
