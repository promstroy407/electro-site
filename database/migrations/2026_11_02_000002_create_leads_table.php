<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Заявки с сайта. Храним только то, что человек ввёл сам, и факт
// согласия на обработку персональных данных (152-ФЗ).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table): void {
            $table->id();
            // Услугу могут удалить, заявка от этого не пропадает.
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->string('name', 100);
            // Нормализованный номер: +7XXXXXXXXXX или + и 10–15 цифр.
            $table->string('phone', 20);
            $table->string('email', 255)->nullable();
            $table->text('message')->nullable();
            // Значения совпадают с App\Enums\LeadStatus.
            $table->enum('status', ['new', 'in_progress', 'closed'])->default('new');
            $table->text('manager_note')->nullable();
            $table->string('source_url', 2048)->nullable();
            $table->timestamp('consent_at');
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        DB::statement("ALTER TABLE leads ADD CONSTRAINT leads_phone_format CHECK (phone ~ '^\\+[0-9]{10,15}$')");
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
