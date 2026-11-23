<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Поправки владельца к подписям фото. Строка меняется, только если
// подпись ещё не трогали в админке.
return new class extends Migration
{
    /** @var list<array{path: string, old: array{caption: string, category: string}, new: array{caption: string, category: string}}> */
    private const FIXES = [
        [
            'path' => 'gallery/06-shchity-ucheta-v-elektroshchitovoy.jpg',
            'old' => ['caption' => 'Щиты учёта в электрощитовой, два ряда', 'category' => 'shchity'],
            'new' => ['caption' => 'Шкафы управления дымоудалением, два ряда', 'category' => 'pozhar'],
        ],
        [
            'path' => 'gallery/44-ru-10kv-kru.jpg',
            'old' => ['caption' => 'Ячейки распределительного устройства', 'category' => 'shchity'],
            'new' => ['caption' => 'Распределительное устройство 10 кВ RM6', 'category' => 'shchity'],
        ],
        [
            'path' => 'gallery/33-transheya-pod-kabel.jpg',
            'old' => ['caption' => 'Траншея под кабель', 'category' => 'kabel'],
            'new' => ['caption' => 'Контур заземления в траншее', 'category' => 'kabel'],
        ],
        [
            'path' => 'gallery/07-pomeshchenie-ozds-koridor.jpg',
            'old' => ['caption' => 'Коридор у помещения оборудования противопожарной защиты', 'category' => 'pozhar'],
            'new' => ['caption' => 'Коридор у помещения противопожарного оборудования, красная полоса — защита от грызунов', 'category' => 'pozhar'],
        ],
    ];

    public function up(): void
    {
        foreach (self::FIXES as $fix) {
            $this->apply($fix['path'], $fix['old'], $fix['new']);
        }
    }

    public function down(): void
    {
        foreach (self::FIXES as $fix) {
            $this->apply($fix['path'], $fix['new'], $fix['old']);
        }
    }

    /**
     * @param  array{caption: string, category: string}  $from
     * @param  array{caption: string, category: string}  $to
     */
    private function apply(string $path, array $from, array $to): void
    {
        DB::table('gallery_photos')
            ->where('path', $path)
            ->where('caption', $from['caption'])
            ->update($to);
    }
};
