<?php

declare(strict_types=1);

use App\Models\Lead;
use App\Models\User;

it('сообщает, сколько заявок пришло после последней увиденной', function (): void {
    $this->actingAs(User::factory()->manager()->create());
    $seen = Lead::factory()->create();
    $newer = Lead::factory()->count(2)->create();

    $this->getJson('/admin/zayavki/pulse?after='.$seen->id)
        ->assertOk()
        ->assertExactJson(['latest' => $newer->last()?->id, 'new' => 2]);
});

it('не пускает к пульсу заявок без прав и без входа', function (): void {
    $this->getJson('/admin/zayavki/pulse?after=0')->assertUnauthorized();

    $this->actingAs(User::factory()->editor()->create());
    $this->getJson('/admin/zayavki/pulse?after=0')->assertForbidden();
});

it('проверяет номер последней заявки', function (string $after): void {
    $this->actingAs(User::factory()->manager()->create());

    $this->getJson('/admin/zayavki/pulse?after='.$after)->assertUnprocessable();
})->with([
    'пусто' => [''],
    'не число' => ['abc'],
    'отрицательное' => ['-1'],
]);

it('подключает звук о заявках тем, кто видит заявки', function (): void {
    $this->actingAs(User::factory()->manager()->create());

    $this->get('/admin')->assertOk()->assertSee('data-lead-alert', escape: false);
});

it('не подключает звук о заявках тем, кому заявки не положены', function (): void {
    $this->actingAs(User::factory()->editor()->create());

    $this->get('/admin')->assertOk()->assertDontSee('data-lead-alert', escape: false);
});
