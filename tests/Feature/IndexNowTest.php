<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

const INDEXNOW_KEY = 'test-key-1234567890';

beforeEach(function (): void {
    config()->set('services.indexnow.key', INDEXNOW_KEY);
});

it('отдаёт файл ключа IndexNow только по своему ключу', function (): void {
    $this->get('/'.INDEXNOW_KEY.'.txt')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
        ->assertSeeText(INDEXNOW_KEY);

    $this->get('/chuzhoy-klyuch-123.txt')->assertNotFound();
});

it('не отдаёт файл ключа, пока ключ не задан', function (): void {
    config()->set('services.indexnow.key', '');

    $this->get('/'.INDEXNOW_KEY.'.txt')->assertNotFound();
});

it('отправляет Яндексу страницы из карты сайта и не повторяет их', function (): void {
    Http::fake(['yandex.com/indexnow' => Http::response('', 200)]);

    $this->artisan('seo:indexnow')->assertSuccessful();

    Http::assertSent(function (Request $request): bool {
        $urls = $request['urlList'];

        return $request->url() === 'https://yandex.com/indexnow'
            && $request['key'] === INDEXNOW_KEY
            && $request['host'] === 'localhost'
            && is_array($urls)
            && in_array('http://localhost/stati/kak-vybrat-kabel', $urls, true);
    });
    expect(DB::table('indexnow_submissions')->where('url', 'http://localhost/stati/kak-vybrat-kabel')->exists())->toBeTrue();

    $this->artisan('seo:indexnow')->expectsOutputToContain('Новых страниц нет')->assertSuccessful();
    Http::assertSentCount(1);
});

it('отправляет только новые страницы', function (): void {
    Http::fake(['yandex.com/indexnow' => Http::response('', 200)]);
    $this->artisan('seo:indexnow')->assertSuccessful();
    DB::table('indexnow_submissions')->where('url', 'http://localhost/stati/uzo-ili-difavtomat')->delete();

    $this->artisan('seo:indexnow')->assertSuccessful();

    Http::assertSentCount(2);
    Http::assertSent(fn (Request $request): bool => $request['urlList'] === ['http://localhost/stati/uzo-ili-difavtomat']);
});

it('при отказе Яндекса ничего не запоминает и сообщает об ошибке', function (): void {
    Http::fake(['yandex.com/indexnow' => Http::response('', 422)]);

    $this->artisan('seo:indexnow')->expectsOutputToContain('код 422')->assertFailed();

    expect(DB::table('indexnow_submissions')->count())->toBe(0);
});

it('без ключа ничего не отправляет', function (): void {
    config()->set('services.indexnow.key', 'плохой');
    Http::fake();

    $this->artisan('seo:indexnow')->expectsOutputToContain('IndexNow выключен')->assertSuccessful();

    Http::assertNothingSent();
});

it('в режиме проверки только печатает адреса', function (): void {
    Http::fake();

    $this->artisan('seo:indexnow --dry-run')
        ->expectsOutputToContain('http://localhost/stati/kak-vybrat-kabel')
        ->assertSuccessful();

    Http::assertNothingSent();
    expect(DB::table('indexnow_submissions')->count())->toBe(0);
});
