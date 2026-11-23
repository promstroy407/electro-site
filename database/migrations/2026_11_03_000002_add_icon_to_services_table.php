<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Иллюстрация направления. Значения совпадают с App\Enums\ServiceIcon;
// пусто — на сайте показывается общая иконка.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->enum('icon', ['elektromontazh', 'slabotochka', 'pozharka', 'video', 'skud', 'ohrana', 'obshchee'])
                ->nullable()
                ->after('audience');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table): void {
            $table->dropColumn('icon');
        });
    }
};
