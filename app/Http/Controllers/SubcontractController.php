<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Services\ServiceCatalog;
use Illuminate\Contracts\View\View;

/**
 * «Субподряд для генподрядчиков» — посадочная страница под запросы
 * компаний (SEO-план, месяц 1). Тексты — в шаблоне, услуги в форме
 * заявки — «для бизнеса» и общие, как на главной.
 */
final readonly class SubcontractController
{
    public function __construct(
        private ServiceCatalog $catalog,
    ) {}

    public function __invoke(): View
    {
        return view('subcontract', [
            'directions' => $this->catalog->directions(Audience::Business),
        ]);
    }
}
