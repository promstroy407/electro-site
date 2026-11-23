<?php

declare(strict_types=1);

use App\Models\GalleryPhoto;
use App\Models\Lead;
use App\Models\NotificationSettings;
use App\Models\PriceItem;
use App\Models\Project;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

it('заливает демо-данные и при повторном запуске ничего не дублирует', function (): void {
    Storage::fake('public');
    config()->set('site.notify_email', 'owner@example.test');

    $this->seed(DatabaseSeeder::class);
    $services = Service::query()->count();
    $users = User::query()->count();
    $leads = Lead::query()->count();
    $prices = PriceItem::query()->count();
    $projects = Project::query()->count();

    $this->seed(DatabaseSeeder::class);

    expect($services)->toBeGreaterThan(10)
        ->and(Service::query()->count())->toBe($services)
        ->and(User::query()->count())->toBe($users)
        ->and($leads)->toBe(3)
        ->and(Lead::query()->count())->toBe($leads)
        ->and($prices)->toBeGreaterThan(20)
        ->and(PriceItem::query()->count())->toBe($prices)
        ->and($projects)->toBe(3)
        ->and(Project::query()->count())->toBe($projects)
        ->and(NotificationSettings::query()->count())->toBe(1)
        ->and(NotificationSettings::query()->sole()->emails)->toBe(['owner@example.test'])
        ->and(GalleryPhoto::query()->count())->toBe(54)
        ->and(User::query()->where('email', AdminUserSeeder::EMAIL)->exists())->toBeTrue();
});

it('строит демо-каталог не глубже двух уровней и публикует его', function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    $tooDeep = Service::query()
        ->whereNotNull('parent_id')
        ->whereHas('parent', fn ($query) => $query->whereNotNull('parent_id'))
        ->count();

    expect($tooDeep)->toBe(0);
    $this->get('/uslugi')->assertOk()->assertSee('Видеонаблюдение')->assertSee('СКУД');
});

it('кладёт демо-картинки объектов на диск и показывает их', function (): void {
    Storage::fake('public');
    $this->seed(DatabaseSeeder::class);

    Storage::disk('public')->assertExists('portfolio/demo/sklad.svg');
    $this->get('/obekty')->assertOk()->assertSee('Демо: склад 1200 м²');
});
