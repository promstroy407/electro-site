<?php

declare(strict_types=1);

use App\Enums\LeadOrigin;
use App\Jobs\SendLeadNotifications;
use App\Mail\LeadNotificationMail;
use App\Models\Lead;
use App\Models\Service;
use App\Services\LeadNotifications\LeadNotifier;
use App\Services\NotificationSettingsStore;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    Mail::fake();
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true, 'result' => []])]);
    config()->set('services.telegram.bot_token', '123:SECRET');

    app(NotificationSettingsStore::class)->current()->update([
        'emails' => ['boss@example.test', 'manager@example.test'],
        'mail_enabled' => true,
        'telegram_enabled' => true,
        'telegram_chat_id' => '555',
    ]);
});

it('отправляет заявку на почту и в Telegram', function (): void {
    $service = Service::factory()->create(['title' => 'Монтаж СКУД']);
    $lead = Lead::factory()->forService($service)->create(['name' => 'Иван', 'phone' => '+79170001122', 'origin' => LeadOrigin::Avito, 'organization' => 'ООО «Стройка»', 'facility' => 'Склад 2 000 м²']);

    $results = app(LeadNotifier::class)->notifyAboutLead($lead);

    expect($results)->toBe(['Почта' => true, 'Telegram' => true]);

    Mail::assertSent(LeadNotificationMail::class, function (LeadNotificationMail $mail) use ($lead): bool {
        return $mail->hasTo('boss@example.test')
            && $mail->hasTo('manager@example.test')
            && str_contains($mail->leadMessage->body, '+79170001122')
            && str_contains($mail->leadMessage->body, 'Монтаж СКУД')
            && str_contains($mail->leadMessage->body, 'Откуда узнал: Авито')
            && str_contains($mail->leadMessage->body, 'Организация: ООО «Стройка»')
            && str_contains($mail->leadMessage->body, 'Объект: Склад 2 000 м²')
            && str_contains($mail->leadMessage->body, '/admin/leads/'.$lead->id);
    });

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://api.telegram.org/bot123:SECRET/sendMessage'
        && $request['chat_id'] === '555'
        && str_contains((string) $request['text'], 'Иван'));
});

it('пропускает выключенные и ненастроенные каналы', function (array $settings, ?string $token, array $expected): void {
    config()->set('services.telegram.bot_token', $token);
    app(NotificationSettingsStore::class)->current()->update($settings);

    expect(app(LeadNotifier::class)->notifyAboutLead(Lead::factory()->create()))->toBe($expected);
})->with([
    'нет токена бота' => [[], '', ['Почта' => true]],
    'Telegram выключен' => [['telegram_enabled' => false], '123:SECRET', ['Почта' => true]],
    'нет адресов почты' => [['emails' => []], '123:SECRET', ['Telegram' => true]],
    'почта выключена' => [['mail_enabled' => false], '123:SECRET', ['Telegram' => true]],
]);

it('не теряет почту, если Telegram упал, и не пишет токен в журнал', function (): void {
    Http::swap(new Illuminate\Http\Client\Factory);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => false], 500)]);
    $log = Mockery::spy(Psr\Log\LoggerInterface::class);
    app()->instance(Psr\Log\LoggerInterface::class, $log);

    $results = app(LeadNotifier::class)->notifyAboutLead(Lead::factory()->create());

    expect($results)->toBe(['Почта' => true, 'Telegram' => false]);
    Mail::assertSent(LeadNotificationMail::class);
    $log->shouldHaveReceived('error')->withArgs(function (string $message, array $context): bool {
        return $context['channel'] === 'Telegram' && ! str_contains((string) $context['error'], 'SECRET');
    });
});

it('задача из очереди отправляет уведомления и молча пропускает удалённую заявку', function (): void {
    $lead = Lead::factory()->create();

    (new SendLeadNotifications($lead->id))->handle(app(LeadNotifier::class));
    Mail::assertSentCount(1);

    (new SendLeadNotifications(999999))->handle(app(LeadNotifier::class));
    Mail::assertSentCount(1);
});
