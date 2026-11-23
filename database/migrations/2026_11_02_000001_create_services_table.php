<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Услуги: дерево из двух уровней — направление (parent_id = null)
// и услуга внутри него. Глубину проверяет App\Rules\TwoLevelParent.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            // Нельзя удалить направление, пока в нём есть услуги.
            $table->foreignId('parent_id')->nullable()->constrained('services')->restrictOnDelete();
            $table->string('title', 150);
            $table->string('slug', 160)->unique();
            $table->string('summary', 300)->nullable();
            $table->text('body')->nullable();
            // Значения совпадают с App\Enums\Audience.
            $table->enum('audience', ['all', 'business', 'home'])->default('all');
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_published')->default(false);
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });

        DB::statement('ALTER TABLE services ADD CONSTRAINT services_not_own_parent CHECK (parent_id IS NULL OR parent_id <> id)');
        DB::statement('ALTER TABLE services ADD CONSTRAINT services_sort_order_non_negative CHECK (sort_order >= 0)');
        DB::statement("ALTER TABLE services ADD CONSTRAINT services_slug_format CHECK (slug ~ '^[a-z0-9]+(-[a-z0-9]+)*$')");
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
