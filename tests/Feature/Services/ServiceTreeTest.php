<?php

declare(strict_types=1);

use App\Models\Service;
use App\Services\MarkdownRenderer;
use App\Services\ServiceTree;

it('предлагает родителем только направления и не предлагает саму услугу', function (): void {
    $direction = Service::factory()->create(['title' => 'Электромонтаж']);
    $other = Service::factory()->create(['title' => 'СКУД']);
    $child = Service::factory()->childOf($direction)->create(['title' => 'Щиты']);

    $options = app(ServiceTree::class)->parentOptions($direction);

    expect($options)->toHaveKey($other->id)
        ->not->toHaveKey($direction->id)
        ->not->toHaveKey($child->id);
});

it('превращает пустой текст в пустую строку', function (?string $markdown): void {
    expect((string) app(MarkdownRenderer::class)->toHtml($markdown))->toBe('');
})->with([null, '', "  \n "]);
