<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Партнёры (решение владельца 20.11): страница /partnery и блок на главной.
// Логотип — на диске public в папке partners.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100);
            $table->string('url', 255)->nullable();
            $table->string('description', 300)->nullable();
            $table->string('logo_path', 255)->nullable();
            $table->boolean('is_published')->default(true);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'sort_order']);
        });

        DB::statement('ALTER TABLE partners ADD CONSTRAINT partners_sort_order_non_negative CHECK (sort_order >= 0)');
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
