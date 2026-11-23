<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Фотогалерея «Реальные фото с наших объектов». Файл — на диске public
// в папке gallery, одна строка — одно фото.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gallery_photos', function (Blueprint $table): void {
            $table->id();
            $table->string('path', 255)->unique();
            $table->string('caption', 200);
            $table->string('category', 20);
            // Где снято: «Торговый центр», «Цех сортировки». Пусто — не подписываем.
            $table->string('place', 100)->nullable();
            $table->boolean('is_published')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        DB::statement("ALTER TABLE gallery_photos ADD CONSTRAINT gallery_photos_category_valid CHECK (category IN ('kabel', 'shchity', 'avtomatika', 'svet', 'pozhar'))");
        DB::statement('ALTER TABLE gallery_photos ADD CONSTRAINT gallery_photos_sort_order_non_negative CHECK (sort_order >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
    }
};
