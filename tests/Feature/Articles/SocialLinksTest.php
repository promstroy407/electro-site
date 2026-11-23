<?php

declare(strict_types=1);

use App\Support\SiteInfo;

/** Подменяет данные сайта: SiteInfo — синглтон, собранный из конфига. */
function useSocialLinks(string $max, string $vk): void
{
    config()->set('site.max_channel', $max);
    config()->set('site.vk', $vk);
    app()->forgetInstance(SiteInfo::class);
}

it('ведёт на канал MAX и группу ВК из подвала, списка статей и статьи', function (): void {
    useSocialLinks('https://max.ru/join/test-kanal', 'https://vk.ru/test-gruppa');

    $this->get('/stati')->assertOk()
        ->assertSee('Короткие советы два раза в неделю')
        ->assertSee('href="https://vk.ru/test-gruppa"', escape: false);
    $this->get('/stati/kak-vybrat-kabel')->assertOk()
        ->assertSee('Советы электрика')
        ->assertSee('href="https://max.ru/join/test-kanal"', escape: false)
        ->assertSee('href="https://vk.ru/test-gruppa"', escape: false);
    $this->get('/')->assertOk()->assertSee('Канал в MAX')->assertSee('Группа ВКонтакте');
});

it('ставит канал и группу в блок контактов на главной рядом с Telegram', function (): void {
    config()->set('site.telegram', 'https://t.me/+79371124897');
    useSocialLinks('https://max.ru/join/test-kanal', 'https://vk.ru/test-gruppa');

    $html = $this->get('/')->assertOk()->getContent();
    preg_match('#<div class="messengers">(.*?)</div>#s', $html, $block);

    expect($block[1] ?? '')->toContain('Написать в Telegram')
        ->toContain('href="https://max.ru/join/test-kanal"')
        ->toContain('href="https://vk.ru/test-gruppa"');
});

it('показывает только заданные ссылки', function (): void {
    useSocialLinks('', 'https://vk.ru/test-gruppa');

    $this->get('/stati/kak-vybrat-kabel')->assertOk()
        ->assertSee('Группа ВКонтакте')
        ->assertDontSee('Канал в MAX');
});

it('ничего не показывает, пока ссылок нет', function (): void {
    useSocialLinks('', '');

    $this->get('/stati')->assertOk()->assertDontSee('Короткие советы два раза в неделю');
    $this->get('/stati/kak-vybrat-kabel')->assertOk()->assertDontSee('Советы электрика');
    $this->get('/')->assertOk()->assertDontSee('Группа ВКонтакте')->assertDontSee('Канал в MAX');
});

it('не вставляет в ссылки чужие адреса', function (): void {
    useSocialLinks('https://evil.example.com/max.ru/', 'https://vk.ru.evil.example.com/x');

    $this->get('/stati/kak-vybrat-kabel')->assertOk()->assertDontSee('evil.example.com');
});
