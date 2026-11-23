<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PartnerCatalog;
use Illuminate\Contracts\View\View;

final readonly class PartnerController
{
    public function __construct(
        private PartnerCatalog $partners,
    ) {}

    public function __invoke(): View
    {
        return view('partners.index', [
            'partners' => $this->partners->list(),
        ]);
    }
}
