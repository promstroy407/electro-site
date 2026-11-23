<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Ставки калькулятора «Предварительный расчёт»: цена работ за 1 м².
// Посетитель отмечает нужные работы и вводит площадь, сайт считает
// площадь × сумму ставок. Ставки меняют в админке, копейки — целым числом.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculator_rates', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 100);
            $table->bigInteger('rate_kopecks');
            // Отмечена в калькуляторе сразу (основная работа — электрика).
            $table->boolean('is_default')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        DB::statement('ALTER TABLE calculator_rates ADD CONSTRAINT calculator_rates_rate_positive CHECK (rate_kopecks > 0)');
        DB::statement('ALTER TABLE calculator_rates ADD CONSTRAINT calculator_rates_sort_order_non_negative CHECK (sort_order >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('calculator_rates');
    }
};
