<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Несколько демо-заявок, чтобы раздел в админке не был пустым.
 * Только в пустую таблицу: настоящие заявки не трогаем.
 */
final class DemoLeadSeeder extends Seeder
{
    public function run(): void
    {
        if (Lead::query()->exists()) {
            return;
        }

        $cctv = Service::query()->where('slug', 'videonablyudenie-dlya-doma')->first();
        $fire = Service::query()->where('slug', 'aps')->first();

        Lead::factory()->create([
            'name' => 'Демо: Ирина',
            'phone' => '+79000000001',
            'email' => null,
            'message' => 'Частный дом, нужно 4 камеры по периметру и просмотр с телефона.',
            'service_id' => $cctv?->id,
            'status' => LeadStatus::New,
            'origin' => LeadOrigin::YandexMaps,
        ]);

        Lead::factory()->create([
            'name' => 'Демо: ООО «Склад»',
            'phone' => '+79000000002',
            'email' => 'sklad@example.test',
            'organization' => 'ООО «Демо-Склад»',
            'facility' => 'Склад 1 200 м², Йошкар-Ола',
            'message' => 'Склад 1200 м², нужна пожарная сигнализация по проекту.',
            'service_id' => $fire?->id,
            'status' => LeadStatus::InProgress,
            'origin' => LeadOrigin::Recommendation,
            'manager_note' => 'Выезд на замер в четверг.',
        ]);

        Lead::factory()->create([
            'name' => 'Демо: Алексей',
            'phone' => '+79000000003',
            'email' => null,
            'message' => 'Замена проводки в двушке.',
            'service_id' => null,
            'status' => LeadStatus::Closed,
            'origin' => LeadOrigin::TwoGis,
        ]);
    }
}
