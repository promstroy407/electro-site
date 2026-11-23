<?php

declare(strict_types=1);

use App\Models\Project;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('удаляет с диска фото, убранные из объекта, и не трогает оставшиеся', function (): void {
    Storage::disk('public')->put('portfolio/a.jpg', 'a');
    Storage::disk('public')->put('portfolio/b.jpg', 'b');
    $project = Project::factory()->withPhotos(['portfolio/a.jpg', 'portfolio/b.jpg'])->create();

    $project->update(['photos' => ['portfolio/b.jpg']]);

    Storage::disk('public')->assertMissing('portfolio/a.jpg');
    Storage::disk('public')->assertExists('portfolio/b.jpg');
});

it('удаляет все фото вместе с объектом', function (): void {
    Storage::disk('public')->put('portfolio/a.jpg', 'a');
    $project = Project::factory()->withPhotos(['portfolio/a.jpg'])->create();

    $project->delete();

    Storage::disk('public')->assertMissing('portfolio/a.jpg');
});

it('не удаляет файлы вне папки портфолио', function (): void {
    Storage::disk('public')->put('other/secret.txt', 'x');
    $project = Project::factory()->withPhotos(['other/secret.txt', 'portfolio/../other/secret.txt'])->create();

    $project->delete();

    Storage::disk('public')->assertExists('other/secret.txt');
});
