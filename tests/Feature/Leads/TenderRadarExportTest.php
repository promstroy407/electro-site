<?php

declare(strict_types=1);

use App\Enums\LeadOrigin;
use App\Jobs\ExportLeadToTenderRadar;
use App\Jobs\SendLeadNotifications;
use App\Models\Lead;
use App\Models\Service;
use App\Services\TenderRadar\TenderRadarExporter;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;

const RADAR_URL = 'https://tender.example.test/api/leads';
const RADAR_TOKEN = 'test-radar-token';

function enableTenderRadar(): void
{
    config([
        'services.tender_radar.leads_url' => RADAR_URL,
        'services.tender_radar.leads_token' => RADAR_TOKEN,
    ]);
}

it('передаёт заявку в Тендер-Радар со всеми полями и токеном', function (): void {
    enableTenderRadar();
    Http::fake([RADAR_URL => Http::response(['deal_id' => 77], 201)]);
    $service = Service::factory()->create(['title' => 'Видеонаблюдение']);
    $lead = Lead::factory()->create([
        'service_id' => $service->id,
        'name' => 'Иван',
        'phone' => '+79170001122',
        'organization' => 'ООО «Стройка»',
        'facility' => 'Склад 2 000 м²',
        'origin' => LeadOrigin::TwoGis,
        'message' => 'Нужны камеры',
    ]);

    expect(app(TenderRadarExporter::class)->export($lead))->toBe(77);

    Http::assertSent(function (Request $request) use ($lead): bool {
        return $request->url() === RADAR_URL
            && $request->hasHeader('Authorization', 'Bearer '.RADAR_TOKEN)
            && $request['external_id'] === 'es12-'.$lead->id
            && $request['name'] === 'Иван'
            && $request['phone'] === '+79170001122'
            && $request['organization'] === 'ООО «Стройка»'
            && $request['facility'] === 'Склад 2 000 м²'
            && $request['service'] === 'Видеонаблюдение'
            && $request['origin'] === '2ГИС'
            && $request['message'] === 'Нужны камеры'
            && $request['estimate'] === null
            && preg_match('/[+-]\d{2}:\d{2}$|Z$/', (string) $request['received_at']) === 1;
    });
});

it('считает повтор той же заявки успехом: Тендер-Радар отвечает 200 без дубля', function (): void {
    enableTenderRadar();
    Http::fake([RADAR_URL => Http::response(['deal_id' => 5], 200)]);

    expect(app(TenderRadarExporter::class)->export(Lead::factory()->create()))->toBe(5);
});

it('при ошибке Тендер-Радара бросает исключение без токена в тексте', function (): void {
    enableTenderRadar();
    Http::fake([RADAR_URL => Http::response(['message' => 'Unauthenticated. '.RADAR_TOKEN], 401)]);

    try {
        app(TenderRadarExporter::class)->export(Lead::factory()->create());
        $this->fail('Ожидалось исключение');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toStartWith('Тендер-Радар:')
            ->not->toContain(RADAR_TOKEN);
    }
});

it('не принимает адрес без https', function (): void {
    config([
        'services.tender_radar.leads_url' => 'http://tender.example.test/api/leads',
        'services.tender_radar.leads_token' => RADAR_TOKEN,
    ]);
    Http::fake();

    expect(fn () => app(TenderRadarExporter::class)->export(Lead::factory()->create()))
        ->toThrow(RuntimeException::class, 'TENDER_RADAR_LEADS_URL');
    Http::assertNothingSent();
});

it('ставит передачу в очередь вместе с уведомлениями, когда Тендер-Радар настроен', function (): void {
    enableTenderRadar();
    Bus::fake();

    $this->post('/zayavka', leadPayload())->assertRedirect();

    $lead = Lead::query()->sole();
    Bus::assertDispatched(SendLeadNotifications::class);
    Bus::assertDispatched(ExportLeadToTenderRadar::class, fn (ExportLeadToTenderRadar $job): bool => $job->leadId === $lead->id);
});

it('без адреса и токена в .env ничего в Тендер-Радар не шлёт', function (): void {
    config(['services.tender_radar.leads_url' => '', 'services.tender_radar.leads_token' => '']);
    Bus::fake();

    $this->post('/zayavka', leadPayload())->assertRedirect();

    Bus::assertDispatched(SendLeadNotifications::class);
    Bus::assertNotDispatched(ExportLeadToTenderRadar::class);
});

it('задача очереди передаёт заявку и молчит для удалённой заявки', function (): void {
    enableTenderRadar();
    Http::fake([RADAR_URL => Http::response(['deal_id' => 1], 201)]);
    $lead = Lead::factory()->create();

    (new ExportLeadToTenderRadar($lead->id))->handle(app(TenderRadarExporter::class));
    (new ExportLeadToTenderRadar(999_999))->handle(app(TenderRadarExporter::class));

    Http::assertSentCount(1);
});
