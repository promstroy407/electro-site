<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;

/**
 * Демо-партнёры для локальной разработки и скриншотов CI. Названия
 * выдуманные: настоящих партнёров добавляет владелец в админке.
 * Вызывают DatabaseSeeder (не на проде) и CI; ContentSeeder — нет.
 */
final class DemoPartnerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->partners() as $order => [$name, $url, $description]) {
            Partner::query()->firstOrCreate(['name' => $name], [
                'url' => $url,
                'description' => $description,
                'is_published' => true,
                'sort_order' => $order,
            ]);
        }
    }

    /**
     * @return list<array{string, string, string}>
     */
    private function partners(): array
    {
        return [
            ['ООО «Демо Строй»', 'https://demo-stroy.example', 'Строительная компания: склады и производственные здания'],
            ['ООО «Пример Монолит»', 'https://primer-monolit.example', 'Монолитные работы и фундаменты'],
            ['ООО «Образец Девелопмент»', 'https://obrazec.example', 'Застройщик коммерческой недвижимости'],
        ];
    }
}
