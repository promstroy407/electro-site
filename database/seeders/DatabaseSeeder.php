<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Демо-данные. Сидеры идемпотентны: повторный запуск ничего не дублирует.
 */
final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Демо-данные на проде не заливаются.');

            return;
        }

        $this->call([
            AdminUserSeeder::class,
            ServiceCatalogSeeder::class,
            GallerySeeder::class,
            GalleryVideosSeeder::class,
            PriceListSeeder::class,
            DemoProjectSeeder::class,
            DemoPartnerSeeder::class,
            NotificationSettingsSeeder::class,
            DemoLeadSeeder::class,
        ]);
    }
}
