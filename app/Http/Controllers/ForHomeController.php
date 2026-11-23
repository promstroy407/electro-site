<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Services\ArticleCatalog;
use App\Services\GalleryCatalog;
use App\Services\ServiceCatalog;
use Illuminate\Contracts\View\View;

/**
 * Раздел «Для дома» — электрика в квартире и частном доме (решение
 * владельца 18.11: частникам раздел на этом сайте, не отдельный сайт).
 * Услуги в форме — «для дома» и общие, статьи — все, кроме статей для компаний.
 */
final readonly class ForHomeController
{
    private const PHOTOS = 9;

    public function __construct(
        private ServiceCatalog $catalog,
        private ArticleCatalog $articles,
        private GalleryCatalog $gallery,
    ) {}

    public function __invoke(): View
    {
        return view('for-home', [
            'directions' => $this->catalog->directions(Audience::Home),
            'articles' => $this->articles->forHome(limit: 3),
            // Примеры работ — фото квартир и домов (в админке «Для кого: для дома»).
            'photos' => $this->gallery->stills($this->gallery->photos(audience: Audience::Home), self::PHOTOS),
        ]);
    }
}
