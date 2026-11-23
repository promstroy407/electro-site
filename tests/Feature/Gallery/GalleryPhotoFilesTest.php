<?php

declare(strict_types=1);

use App\Models\GalleryPhoto;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('public');
});

it('удаляет старый файл, когда фото заменили', function (): void {
    Storage::disk('public')->put('gallery/old.jpg', 'a');
    Storage::disk('public')->put('gallery/new.jpg', 'b');
    $photo = GalleryPhoto::factory()->create(['path' => 'gallery/old.jpg']);

    $photo->update(['path' => 'gallery/new.jpg']);

    Storage::disk('public')->assertMissing('gallery/old.jpg');
    Storage::disk('public')->assertExists('gallery/new.jpg');
});

it('удаляет файл вместе с фото', function (): void {
    Storage::disk('public')->put('gallery/a.jpg', 'a');
    $photo = GalleryPhoto::factory()->create(['path' => 'gallery/a.jpg']);

    $photo->delete();

    Storage::disk('public')->assertMissing('gallery/a.jpg');
});

it('не удаляет файлы вне папки галереи', function (): void {
    Storage::disk('public')->put('portfolio/a.jpg', 'a');
    $photo = GalleryPhoto::factory()->create(['path' => 'gallery/../portfolio/a.jpg']);

    $photo->delete();

    Storage::disk('public')->assertExists('portfolio/a.jpg');
});

it('удаляет вместе с видео и его обложку', function (): void {
    Storage::disk('public')->put('gallery/v.mp4', 'v');
    Storage::disk('public')->put('gallery/v.jpg', 'p');
    $video = GalleryPhoto::factory()->create(['path' => 'gallery/v.mp4', 'poster_path' => 'gallery/v.jpg']);

    $video->delete();

    Storage::disk('public')->assertMissing(['gallery/v.mp4', 'gallery/v.jpg']);
});
