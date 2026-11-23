<?php

declare(strict_types=1);

use App\Filament\Resources\Leads\Pages\EditLead;
use App\Jobs\SendLeadNotifications;
use App\Models\CalculatorRate;
use App\Models\Lead;
use App\Models\User;
use App\Services\Calculator;
use App\Services\LeadNotifications\LeadMessageFormatter;
use App\Support\Estimate;
use App\Support\EstimateItem;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function estimatePayload(array $rateIds, array $overrides = []): array
{
    return array_merge([
        'estimate_area' => '60',
        'estimate_rates' => $rateIds,
        'name' => 'Иван',
        'phone' => '8 (917) 000-11-22',
        'consent' => '1',
        'company_website' => '',
    ], $overrides);
}

beforeEach(function (): void {
    Bus::fake();
});

it('считает площадь × сумму ставок в копейках без потерь', function (): void {
    $estimate = new Estimate(73, [new EstimateItem('Электрика', 150_050), new EstimateItem('Видео', 50_000)]);

    expect($estimate->ratePerM2Kopecks())->toBe(200_050)
        ->and($estimate->totalKopecks())->toBe(14_603_650);
});

it('не принимает площадь вне границ', function (int $area): void {
    new Estimate($area, [new EstimateItem('Электрика', 150_000)]);
})->with([0, 100_001])->throws(InvalidArgumentException::class);

it('берёт в расчёт только опубликованные ставки', function (): void {
    $power = CalculatorRate::factory()->create(['rate_kopecks' => 150_000]);
    $hidden = CalculatorRate::factory()->unpublished()->create(['rate_kopecks' => 50_000]);

    $estimate = app(Calculator::class)->estimate(10, [$power->id, $hidden->id]);

    expect($estimate->totalKopecks())->toBe(1_500_000)
        ->and($estimate->items)->toHaveCount(1);
});

it('показывает калькулятор на своей странице: работы без ставок за м², ставки — только для расчёта', function (): void {
    CalculatorRate::factory()->create(['title' => 'Электромонтаж под ключ', 'rate_kopecks' => 150_000, 'is_default' => true]);
    CalculatorRate::factory()->create(['title' => 'Видеонаблюдение', 'rate_kopecks' => 50_000]);
    CalculatorRate::factory()->unpublished()->create(['title' => 'Скрытая работа']);

    $this->get('/kalkulyator')
        ->assertOk()
        ->assertSee('id="raschet"', escape: false)
        ->assertSee('Предварительный расчёт')
        ->assertSee('Электромонтаж под ключ')
        ->assertDontSee('за м²')
        ->assertSee('data-calc-rate="150000"', escape: false)
        ->assertDontSee('Скрытая работа');
});

it('без ставок страницы калькулятора нет и ссылок на неё тоже', function (): void {
    $this->get('/kalkulyator')->assertNotFound();
    $this->get('/')->assertOk()->assertDontSee(route('calculator'), escape: false);
});

it('главная для компаний: калькулятора на ней нет, кнопка ведёт к заявке', function (): void {
    CalculatorRate::factory()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee('id="raschet"', escape: false)
        ->assertDontSee(route('calculator'), escape: false)
        ->assertSee('class="btn btn--accent" href="#zayavka"', escape: false);
});

it('сохраняет заявку из калькулятора со снимком ставок и возвращает к расчёту', function (): void {
    $power = CalculatorRate::factory()->create(['title' => 'Электрика', 'rate_kopecks' => 150_000]);
    $video = CalculatorRate::factory()->create(['title' => 'Видео', 'rate_kopecks' => 50_000]);

    $this->from('/kalkulyator')
        ->post('/zayavka', estimatePayload([$power->id, $video->id]))
        ->assertRedirect(url('/kalkulyator').'#raschet')
        ->assertSessionHas('lead_form', 'estimate');

    $lead = Lead::query()->sole();
    expect($lead->estimate_area_m2)->toBe(60)
        ->and($lead->estimate()?->totalKopecks())->toBe(12_000_000);

    // Ставку поменяли — расчёт в заявке остался прежним.
    $power->update(['rate_kopecks' => 200_000]);
    expect($lead->refresh()->estimate()?->totalKopecks())->toBe(12_000_000);

    Bus::assertDispatched(SendLeadNotifications::class);
});

it('пишет расчёт в уведомление о заявке', function (): void {
    $power = CalculatorRate::factory()->create(['title' => 'Электромонтаж под ключ', 'rate_kopecks' => 150_000]);
    $this->post('/zayavka', estimatePayload([$power->id]));

    $body = app(LeadMessageFormatter::class)->forLead(Lead::query()->sole())->body;

    expect($body)->toContain('Предварительный расчёт с сайта:')
        ->toContain('Площадь: 60 м²')
        ->toContain("Электромонтаж под ключ — 1\u{00A0}500\u{00A0}₽ за м²")
        ->toContain("Итого: от 90\u{00A0}000\u{00A0}₽");
});

it('обычная заявка без калькулятора остаётся без расчёта', function (): void {
    $this->post('/zayavka', [
        'name' => 'Иван', 'phone' => '89170001122', 'consent' => '1', 'company_website' => '',
    ])->assertSessionHas('lead_form', 'contact');

    $lead = Lead::query()->sole();
    expect($lead->estimate())->toBeNull()
        ->and($lead->estimate_area_m2)->toBeNull();
});

it('отклоняет неверный расчёт и показывает ошибку у калькулятора', function (Closure $payload, string $field): void {
    $rate = CalculatorRate::factory()->create();
    $hidden = CalculatorRate::factory()->unpublished()->create();

    $this->from('/kalkulyator')
        ->post('/zayavka', $payload($rate->id, $hidden->id))
        ->assertRedirect(url('/kalkulyator').'#raschet')
        ->assertSessionHasErrors([$field], errorBag: 'estimate');

    expect(Lead::query()->count())->toBe(0);
})->with([
    'нулевая площадь' => [fn (int $r): array => estimatePayload([$r], ['estimate_area' => '0']), 'estimate_area'],
    'площадь больше 100 000' => [fn (int $r): array => estimatePayload([$r], ['estimate_area' => '100001']), 'estimate_area'],
    'площадь буквами' => [fn (int $r): array => estimatePayload([$r], ['estimate_area' => 'много']), 'estimate_area'],
    'без площади' => [fn (int $r): array => estimatePayload([$r], ['estimate_area' => '']), 'estimate_area'],
    'ни одной работы' => [fn (int $r): array => estimatePayload([]), 'estimate_rates'],
    'скрытая ставка' => [fn (int $r, int $h): array => estimatePayload([$h]), 'estimate_rates.0'],
    'несуществующая ставка' => [fn (int $r): array => estimatePayload([999999]), 'estimate_rates.0'],
    'без телефона' => [fn (int $r): array => estimatePayload([$r], ['phone' => '']), 'phone'],
    'без согласия' => [fn (int $r): array => estimatePayload([$r], ['consent' => null]), 'consent'],
]);

it('показывает расчёт в карточке заявки в админке', function (): void {
    $rate = CalculatorRate::factory()->create(['title' => 'Электромонтаж под ключ', 'rate_kopecks' => 150_000]);
    $this->post('/zayavka', estimatePayload([$rate->id]));
    $lead = Lead::query()->sole();

    $this->actingAs(User::factory()->manager()->create())
        ->get('/admin/leads/'.$lead->id.'/edit')
        ->assertOk()
        ->assertSee('Предварительный расчёт с сайта');

    Livewire::test(EditLead::class, ['record' => $lead->getRouteKey()])
        ->assertSchemaStateSet(['estimate_summary' => "Площадь: 60 м²\nЭлектромонтаж под ключ — 1\u{00A0}500\u{00A0}₽ за м²\nИтого: от 90\u{00A0}000\u{00A0}₽"]);
});

it('калькулятор остаётся по прямой ссылке, но плавающей кнопки на сайте больше нет', function (string $url): void {
    CalculatorRate::factory()->create();

    $this->get('/kalkulyator')->assertOk();
    $this->get($url)->assertOk()->assertDontSee('data-calc-fab', escape: false);
})->with(['/', '/ceny', '/foto', '/stati', '/uslugi']);

it('добавляет калькулятор в sitemap, только когда есть ставки', function (): void {
    $this->get('/sitemap.xml')->assertOk()->assertDontSee(route('calculator'), escape: false);

    CalculatorRate::factory()->create();

    $this->get('/sitemap.xml')->assertOk()->assertSee(route('calculator'), escape: false);
});
