<?php

declare(strict_types=1);

use App\Enums\MediaKind;

it('определяет видео по расширению файла', function (string $path, MediaKind $expected): void {
    expect(MediaKind::fromPath($path))->toBe($expected);
})->with([
    'mp4' => ['gallery/sdacha.mp4', MediaKind::Video],
    'MP4 заглавными' => ['gallery/SDACHA.MP4', MediaKind::Video],
    'webm' => ['gallery/clip.webm', MediaKind::Video],
    'jpg' => ['gallery/shchit.jpg', MediaKind::Photo],
    'без расширения' => ['gallery/fail', MediaKind::Photo],
]);
