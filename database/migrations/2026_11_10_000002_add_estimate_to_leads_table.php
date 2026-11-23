<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Заявка из калькулятора: площадь и снимок выбранных ставок на момент
// отправки (ставки потом могут поменяться в админке). Итог не храним —
// он считается из площади и ставок (App\Support\Estimate::totalKopecks).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->integer('estimate_area_m2')->nullable();
            // [{"title": "...", "rate_kopecks": 150000}, ...]
            $table->jsonb('estimate_items')->nullable();
        });

        DB::statement('ALTER TABLE leads ADD CONSTRAINT leads_estimate_area_range CHECK (estimate_area_m2 IS NULL OR estimate_area_m2 BETWEEN 1 AND 100000)');
        DB::statement('ALTER TABLE leads ADD CONSTRAINT leads_estimate_complete CHECK ((estimate_area_m2 IS NULL) = (estimate_items IS NULL))');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leads DROP CONSTRAINT leads_estimate_complete');
        DB::statement('ALTER TABLE leads DROP CONSTRAINT leads_estimate_area_range');

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn(['estimate_area_m2', 'estimate_items']);
        });
    }
};
