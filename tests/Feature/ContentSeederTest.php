<?php

declare(strict_types=1);

use App\Models\CalculatorRate;
use App\Models\GalleryPhoto;
use App\Models\PriceItem;
use App\Models\Service;
use Database\Seeders\ContentSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('заливает каталог услуг и все фото галереи вместе с файлами', function (): void {
    $this->seed(ContentSeeder::class);

    expect(Service::query()->count())->toBeGreaterThan(10)
        ->and(GalleryPhoto::query()->count())->toBe(54)
        ->and(GalleryPhoto::query()->where('kind', 'video')->count())->toBe(2)
        ->and(PriceItem::query()->count())->toBeGreaterThan(30)
        ->and(PriceItem::query()->where('is_from', false)->exists())->toBeFalse()
        ->and(CalculatorRate::query()->orderBy('sort_order')->pluck('rate_kopecks')->all())->toBe([150_000, 50_000, 50_000])
        ->and(Service::query()->where('slug', 'vysokovoltnye-raboty-do-10-kv')->exists())->toBeTrue()
        // Обслуживание пожарной сигнализации владелец убрал: нет лицензии.
        ->and(Service::query()->where('title', 'like', '%бслуживание пожарной%')->exists())->toBeFalse();

    foreach (GalleryPhoto::query()->pluck('path') as $path) {
        Storage::disk('public')->assertExists($path);
    }

    $this->get('/foto')->assertOk()->assertSee('ВРУ с автоматическим вводом резерва (АВР)');
});

it('при повторном запуске не дублирует и не возвращает удалённое в админке', function (): void {
    $this->seed(ContentSeeder::class);
    GalleryPhoto::query()->orderBy('id')->firstOrFail()->delete();
    Service::query()->where('slug', 'skud')->update(['title' => 'Контроль доступа']);
    PriceItem::query()->where('title', 'Монтаж IP-камеры')->update(['price_kopecks' => 350_000]);
    CalculatorRate::query()->where('title', 'Видеонаблюдение')->update(['rate_kopecks' => 70_000]);

    $this->seed(ContentSeeder::class);

    expect(GalleryPhoto::query()->count())->toBe(53)
        ->and(Service::query()->where('slug', 'skud')->value('title'))->toBe('Контроль доступа')
        ->and(PriceItem::query()->where('title', 'Монтаж IP-камеры')->value('price_kopecks'))->toBe(350_000)
        ->and(CalculatorRate::query()->count())->toBe(3)
        ->and(CalculatorRate::query()->where('title', 'Видеонаблюдение')->value('rate_kopecks'))->toBe(70_000);
});
