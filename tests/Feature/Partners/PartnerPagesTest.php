<?php

declare(strict_types=1);

use App\Models\Partner;
use Illuminate\Support\Facades\Storage;

it('показывает на /partnery опубликованных партнёров со ссылкой на их сайт', function (): void {
    Partner::factory()->create([
        'name' => 'СК М ГРУПП',
        'url' => 'https://www.sk-m-group.ru',
        'description' => 'Строительная компания, Йошкар-Ола',
    ]);
    Partner::factory()->unpublished()->create(['name' => 'Черновик Строй']);

    $this->get('/partnery')
        ->assertOk()
        ->assertSee('Наши партнёры')
        ->assertSee('СК М ГРУПП')
        ->assertSee('Строительная компания, Йошкар-Ола')
        ->assertSee('href="https://www.sk-m-group.ru" target="_blank" rel="noopener"', escape: false)
        ->assertSee('sk-m-group.ru ↗')
        ->assertDontSee('Черновик Строй');
});

it('показывает партнёров в порядке из админки', function (): void {
    Partner::factory()->create(['name' => 'Второй Строй', 'sort_order' => 2]);
    Partner::factory()->create(['name' => 'Первый Строй', 'sort_order' => 1]);

    $this->get('/partnery')->assertOk()->assertSeeInOrder(['Первый Строй', 'Второй Строй']);
});

it('показывает логотип, а без него — первую букву названия', function (): void {
    Storage::fake('public');
    Partner::factory()->create(['name' => 'С логотипом', 'logo_path' => 'partners/logo.png']);
    Partner::factory()->create(['name' => 'ООО «Без логотипа»', 'url' => null]);

    $this->get('/partnery')
        ->assertOk()
        ->assertSee('partners/logo.png', escape: false)
        ->assertSee('<span class="partner__initial" aria-hidden="true">Б</span>', escape: false);
});

it('без партнёров страница открывается и пишет, что список скоро появится', function (): void {
    $this->get('/partnery')->assertOk()->assertSee('Список партнёров скоро появится');
});

it('показывает блок партнёров на главной, только если они есть', function (): void {
    $this->get('/')->assertOk()->assertDontSee('id="partnery"', escape: false);

    Partner::factory()->create(['name' => 'СК М ГРУПП']);

    $this->get('/')
        ->assertOk()
        ->assertSee('id="partnery"', escape: false)
        ->assertSee('СК М ГРУПП')
        ->assertSee(route('partners'), escape: false);
});

it('на главной не больше шести партнёров', function (): void {
    foreach (range(1, 7) as $n) {
        Partner::factory()->create(['name' => 'Партнёр номер '.$n, 'sort_order' => $n]);
    }

    $this->get('/')
        ->assertOk()
        ->assertSee('Партнёр номер 6')
        ->assertDontSee('Партнёр номер 7');
});

it('добавляет /partnery в карту сайта, только когда есть партнёры', function (): void {
    $this->get('/sitemap.xml')->assertOk()->assertDontSee('/partnery', escape: false);

    Partner::factory()->create();

    $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>http://localhost/partnery</loc>', escape: false);
});

it('ссылка на партнёров есть в подвале', function (): void {
    $this->get('/uslugi')->assertOk()->assertSee('>Партнёры</a>', escape: false);
});
