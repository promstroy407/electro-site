<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Куда отправлять уведомления о заявках. Одна строка на весь сайт,
// меняется администратором в админке. Токены ботов здесь не хранятся:
// это секреты, они в .env.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table): void {
            $table->id();
            $table->jsonb('emails');
            $table->boolean('mail_enabled')->default(true);
            $table->boolean('telegram_enabled')->default(false);
            $table->string('telegram_chat_id', 64)->nullable();
            $table->timestamps();
        });

        DB::table('notification_settings')->insert([
            'emails' => '[]',
            'mail_enabled' => true,
            'telegram_enabled' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
