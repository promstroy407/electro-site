<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PriceCatalog;
use Illuminate\Contracts\View\View;

final readonly class PriceController
{
    public function __construct(
        private PriceCatalog $prices,
    ) {}

    public function index(): View
    {
        return view('prices.index', [
            'directions' => $this->prices->fullList(),
        ]);
    }
}
