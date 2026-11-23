<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Прайс: позиции привязаны к услуге. Цена — копейки целым числом.
// «Цена от» услуги не хранится: она считается как минимум по позициям.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_items', function (Blueprint $table): void {
            $table->id();
            // Удалили услугу — её позиции прайса больше некуда показывать.
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('title', 200);
            // Значения совпадают с App\Enums\PriceUnit.
            $table->enum('unit', ['pcs', 'm', 'm2', 'point', 'set', 'service', 'hour', 'month']);
            $table->bigInteger('price_kopecks');
            $table->boolean('is_from')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index(['service_id', 'sort_order']);
        });

        DB::statement('ALTER TABLE price_items ADD CONSTRAINT price_items_price_positive CHECK (price_kopecks > 0)');
        DB::statement('ALTER TABLE price_items ADD CONSTRAINT price_items_sort_order_non_negative CHECK (sort_order >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('price_items');
    }
};
