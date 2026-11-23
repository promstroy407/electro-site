<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// «Откуда вы о нас узнали?» в заявке. Необязательно: у старых заявок
// и у тех, кто не ответил, — пусто. Значения — App\Enums\LeadOrigin.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table): void {
            $table->string('origin', 20)->nullable()->index();
        });

        DB::statement("ALTER TABLE leads ADD CONSTRAINT leads_origin_valid CHECK (origin IN ('yandex_search', 'yandex_maps', '2gis', 'avito', 'recommendation', 'other'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE leads DROP CONSTRAINT leads_origin_valid');

        Schema::table('leads', function (Blueprint $table): void {
            $table->dropColumn('origin');
        });
    }
};
