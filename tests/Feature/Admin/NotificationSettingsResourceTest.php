<?php

declare(strict_types=1);

use App\Filament\Resources\NotificationSettings\Pages\EditNotificationSettings;
use App\Mail\LeadNotificationMail;
use App\Models\User;
use App\Services\NotificationSettingsStore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

function editSettings(): mixed
{
    $settings = app(NotificationSettingsStore::class)->current();

    return Livewire::test(EditNotificationSettings::class, ['record' => $settings->getRouteKey()]);
}

it('пускает в настройки уведомлений только администратора', function (string $state, int $status): void {
    $settings = app(NotificationSettingsStore::class)->current();
    $this->actingAs(User::factory()->{$state}()->create());

    $this->get('/admin/notification-settings')->assertStatus($status);
    $this->get('/admin/notification-settings/'.$settings->id.'/edit')->assertStatus($status);
})->with([
    'администратор' => ['admin', 200],
    'менеджер' => ['manager', 403],
    'редактор' => ['editor', 403],
]);

describe('правка', function (): void {
    beforeEach(function (): void {
        $this->actingAs(User::factory()->admin()->create());
    });

    it('сохраняет адреса и чат', function (): void {
        editSettings()
            ->fillForm([
                'mail_enabled' => true,
                'emails' => ['promstroy@example.test', 'manager@example.test'],
                'telegram_enabled' => true,
                'telegram_chat_id' => '-1001234567890',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $settings = app(NotificationSettingsStore::class)->current();
        expect($settings->emails)->toBe(['promstroy@example.test', 'manager@example.test'])
            ->and($settings->telegram_chat_id)->toBe('-1001234567890');
    });

    it('не сохраняет кривой адрес почты', function (): void {
        editSettings()
            ->fillForm(['mail_enabled' => true, 'emails' => ['не-почта']])
            ->call('save')
            ->assertHasFormErrors();
    });

    it('требует адрес, если почта включена, и чат, если включён Telegram', function (array $data, string $field): void {
        editSettings()
            ->fillForm($data)
            ->call('save')
            ->assertHasFormErrors([$field]);
    })->with([
        'почта без адресов' => [['mail_enabled' => true, 'emails' => []], 'emails'],
        'Telegram без чата' => [['telegram_enabled' => true, 'telegram_chat_id' => ''], 'telegram_chat_id'],
        'чат в неверном формате' => [['telegram_enabled' => true, 'telegram_chat_id' => 'мой чат'], 'telegram_chat_id'],
    ]);

    it('отправляет тестовое уведомление по сохранённым настройкам', function (): void {
        Mail::fake();
        app(NotificationSettingsStore::class)->current()->update(['emails' => ['boss@example.test'], 'mail_enabled' => true]);

        editSettings()->callAction('sendTest');

        Mail::assertSent(LeadNotificationMail::class, fn (LeadNotificationMail $mail): bool => $mail->hasTo('boss@example.test'));
    });

    it('находит чат Telegram по последнему сообщению боту и включает канал', function (): void {
        config()->set('services.telegram.bot_token', '123:SECRET');
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => [
            ['update_id' => 1, 'message' => ['chat' => ['id' => 111, 'first_name' => 'Старый']]],
            ['update_id' => 2, 'message' => ['chat' => ['id' => 222, 'first_name' => 'Александр']]],
        ]])]);

        editSettings()->callAction('findTelegramChat');

        $settings = app(NotificationSettingsStore::class)->current();
        expect($settings->telegram_chat_id)->toBe('222')
            ->and($settings->telegram_enabled)->toBeTrue();
    });
});
