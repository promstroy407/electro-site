<?php

declare(strict_types=1);

use App\Models\Partner;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('удаляет старый логотип, когда его заменили', function (): void {
    Storage::disk('public')->put('partners/old.png', 'a');
    Storage::disk('public')->put('partners/new.png', 'b');
    $partner = Partner::factory()->create(['logo_path' => 'partners/old.png']);

    $partner->update(['logo_path' => 'partners/new.png']);

    Storage::disk('public')->assertMissing('partners/old.png');
    Storage::disk('public')->assertExists('partners/new.png');
});

it('удаляет логотип, когда его убрали', function (): void {
    Storage::disk('public')->put('partners/a.png', 'a');
    $partner = Partner::factory()->create(['logo_path' => 'partners/a.png']);

    $partner->update(['logo_path' => null]);

    Storage::disk('public')->assertMissing('partners/a.png');
});

it('удаляет логотип вместе с партнёром', function (): void {
    Storage::disk('public')->put('partners/a.png', 'a');
    $partner = Partner::factory()->create(['logo_path' => 'partners/a.png']);

    $partner->delete();

    Storage::disk('public')->assertMissing('partners/a.png');
});

it('не удаляет файлы вне папки партнёров', function (): void {
    Storage::disk('public')->put('gallery/a.jpg', 'a');
    $partner = Partner::factory()->create(['logo_path' => 'partners/../gallery/a.jpg']);

    $partner->delete();

    Storage::disk('public')->assertExists('gallery/a.jpg');
});
