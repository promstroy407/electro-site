<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\Audience;
use App\Services\MarkdownRenderer;
use App\Services\PriceCatalog;
use App\Services\ProjectCatalog;
use App\Services\ServiceCatalog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final readonly class ServiceController
{
    public function __construct(
        private ServiceCatalog $catalog,
        private PriceCatalog $prices,
        private ProjectCatalog $projects,
        private MarkdownRenderer $markdown,
    ) {}

    public function index(Request $request): View
    {
        // Неизвестное значение фильтра — не ошибка, а «всем».
        $audience = Audience::tryFrom($request->string('dlya')->toString()) ?? Audience::All;
        $directions = $this->catalog->directions($audience);

        return view('services.index', [
            'audience' => $audience,
            'directions' => $directions,
            'pricesFrom' => $this->prices->pricesFrom($directions),
        ]);
    }

    public function show(string $slug): View
    {
        $service = $this->catalog->findPublished($slug);

        abort_if($service === null, 404);

        $this->prices->loadInto($service);

        return view('services.show', [
            'service' => $service,
            'bodyHtml' => $this->markdown->toHtml($service->body),
            'priceFrom' => $this->prices->priceFromLoaded($service),
            'projects' => $this->projects->list($service, 3),
            'directions' => $this->catalog->directions(),
        ]);
    }
}
