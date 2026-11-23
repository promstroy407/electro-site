<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\MediaKind;
use App\Models\CalculatorRate;
use App\Models\GalleryPhoto;
use App\Models\PriceItem;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Настоящее наполнение сайта: каталог услуг, фотогалерея, прайс и ставки калькулятора. В отличие от
 * демо-данных, запускается и на проде — при каждом обновлении
 * (deploy/update.sh). Каждая часть заливается только в пустую таблицу:
 * то, что потом удалили или поправили в админке, не возвращается.
 */
final class ContentSeeder extends Seeder
{
    public function run(): void
    {
        if (! Service::query()->exists()) {
            $this->call(ServiceCatalogSeeder::class);
        }

        if (! GalleryPhoto::query()->exists()) {
            $this->call(GallerySeeder::class);
        }

        if (! GalleryPhoto::query()->where('kind', MediaKind::Video)->exists()) {
            $this->call(GalleryVideosSeeder::class);
        }

        if (! PriceItem::query()->exists()) {
            $this->call(PriceListSeeder::class);
        }

        if (! CalculatorRate::query()->exists()) {
            $this->call(CalculatorRateSeeder::class);
        }
    }
}
