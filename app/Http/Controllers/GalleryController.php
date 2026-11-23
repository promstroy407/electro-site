<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\GalleryCatalog;
use Illuminate\Contracts\View\View;

final readonly class GalleryController
{
    public function __construct(
        private GalleryCatalog $gallery,
    ) {}

    public function __invoke(): View
    {
        $photos = $this->gallery->photos();

        return view('gallery.index', [
            'photos' => $photos,
            'categories' => $this->gallery->categories($photos),
            'places' => $this->gallery->places($photos),
        ]);
    }
}
