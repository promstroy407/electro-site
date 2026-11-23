<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Адреса, о которых уже сообщили Яндексу через IndexNow: после каждого
// обновления сайта отправляются только новые страницы из sitemap.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('indexnow_submissions', function (Blueprint $table): void {
            $table->id();
            $table->string('url', 2048)->unique();
            $table->timestampTz('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indexnow_submissions');
    }
};
