<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Services\ArticleCatalog;
use App\Services\GalleryCatalog;
use App\Services\PartnerCatalog;
use App\Services\ProjectCatalog;
use App\Services\SeasonalOffer;
use App\Services\ServiceCatalog;
use Illuminate\Contracts\View\View;

final readonly class HomeController
{
    /** Сколько партнёров на главной; остальные — на /partnery. */
    private const int HOME_PARTNERS = 6;

    public function __construct(
        private ServiceCatalog $catalog,
        private ProjectCatalog $projects,
        private GalleryCatalog $gallery,
        private ArticleCatalog $articles,
        private SeasonalOffer $seasonal,
        private PartnerCatalog $partners,
    ) {}

    public function __invoke(): View
    {
        // Главная — для компаний: услуги «для бизнеса» и общие.
        $directions = $this->catalog->directions(Audience::Business);
        $photos = $this->gallery->photos(audience: Audience::Business);

        return view('home', [
            'directions' => $directions,
            'seasonal' => $this->seasonal->current(now()),
            'projects' => $this->projects->list(limit: 3),
            'photos' => $this->gallery->stills($photos, 6),
            'photoCount' => $photos->count(),
            'placeCards' => $this->gallery->placeCards($photos),
            'articles' => array_slice($this->articles->all(), 0, 3),
            'partners' => $this->partners->list(limit: self::HOME_PARTNERS),
        ]);
    }
}
