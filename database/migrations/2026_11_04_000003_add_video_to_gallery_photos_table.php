<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Видео в галерее: та же таблица, вид — по расширению файла,
// у видео может быть отдельная обложка.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_photos', function (Blueprint $table): void {
            $table->string('kind', 10)->default('photo');
            $table->string('poster_path', 255)->nullable();
        });

        DB::statement("ALTER TABLE gallery_photos ADD CONSTRAINT gallery_photos_kind_valid CHECK (kind IN ('photo', 'video'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE gallery_photos DROP CONSTRAINT gallery_photos_kind_valid');

        Schema::table('gallery_photos', function (Blueprint $table): void {
            $table->dropColumn(['kind', 'poster_path']);
        });
    }
};
