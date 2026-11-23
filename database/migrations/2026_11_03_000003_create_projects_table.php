<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Портфолио: выполненные объекты. Фото — пути к файлам на диске public
// (storage/app/public/portfolio), первое фото — обложка.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->string('title', 150);
            $table->string('slug', 160)->unique();
            $table->string('city', 100);
            $table->smallInteger('year');
            $table->string('summary', 300)->nullable();
            $table->text('body')->nullable();
            $table->jsonb('photos')->default('[]');
            $table->boolean('is_published')->default(false);
            $table->smallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_published', 'year']);
        });

        DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_year_range CHECK (year BETWEEN 2000 AND 2100)');
        DB::statement('ALTER TABLE projects ADD CONSTRAINT projects_sort_order_non_negative CHECK (sort_order >= 0)');
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_slug_format CHECK (slug ~ '^[a-z0-9]+(-[a-z0-9]+)*$')");
        DB::statement("ALTER TABLE projects ADD CONSTRAINT projects_photos_is_array CHECK (jsonb_typeof(photos) = 'array')");

        // Какие работы выполнены на объекте — метки услуг.
        Schema::create('project_service', function (Blueprint $table): void {
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->primary(['project_id', 'service_id']);
            $table->index('service_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_service');
        Schema::dropIfExists('projects');
    }
};
