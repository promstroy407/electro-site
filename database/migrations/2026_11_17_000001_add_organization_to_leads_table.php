<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Сайт переориентирован на компании: в заявке — организация и объект
// («Склад 2 000 м², Йошкар-Ола»). Оба поля необязательные, у старых
// заявок — пусто. Только добавление колонок: данные не теряются.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('organization', 150)->nullable();
            $table->string('facility', 200)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn(['organization', 'facility']);
        });
    }
};
