<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Service;
use App\Services\ProjectPhotos;
use Illuminate\Contracts\Filesystem\Factory as Filesystems;
use Illuminate\Database\Seeder;

/**
 * Демо-объекты портфолио с рисованными картинками вместо фото.
 * Названия начинаются с «Демо:» — владелец заменит их настоящими.
 * Идемпотентен: объект ищется по адресу, картинки копируются один раз.
 */
final class DemoProjectSeeder extends Seeder
{
    private const SOURCE = __DIR__.'/demo-photos';

    public function run(Filesystems $filesystems): void
    {
        $disk = $filesystems->disk(ProjectPhotos::DISK);

        foreach ($this->projects() as $data) {
            $photos = [];
            foreach ($data['photos'] as $file) {
                $path = ProjectPhotos::DIRECTORY.'/demo/'.$file;
                if (! $disk->exists($path)) {
                    $disk->put($path, (string) file_get_contents(self::SOURCE.'/'.$file));
                }
                $photos[] = $path;
            }

            $project = Project::query()->updateOrCreate(['slug' => $data['slug']], [
                'title' => $data['title'],
                'city' => $data['city'],
                'year' => $data['year'],
                'summary' => $data['summary'],
                'body' => $data['body'],
                'photos' => $photos,
                'is_published' => true,
            ]);

            $ids = Service::query()->whereIn('slug', $data['services'])->pluck('id')->all();
            $project->services()->sync($ids);
        }
    }

    /**
     * @return list<array{slug: string, title: string, city: string, year: int, summary: string, body: string, services: list<string>, photos: list<string>}>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'demo-sklad-pozharnaya-signalizatsiya',
                'title' => 'Демо: склад 1200 м², пожарная сигнализация',
                'city' => 'Йошкар-Ола',
                'year' => 2025,
                'summary' => 'Автоматическая пожарная сигнализация и оповещение по проекту, сдача инспекции.',
                'body' => "Пример описания объекта. Замените его настоящим: что было, что сделали, сроки.\n\n- 86 извещателей, 2 приёмно-контрольных прибора;\n- система оповещения 2-го типа;\n- 14 рабочих дней от начала монтажа до сдачи.",
                'services' => ['aps', 'soue'],
                'photos' => ['sklad.svg'],
            ],
            [
                'slug' => 'demo-ofis-pod-klyuch',
                'title' => 'Демо: офис 300 м² под ключ',
                'city' => 'Казань',
                'year' => 2025,
                'summary' => 'Электрика, рабочие места СКС и контроль доступа на входе.',
                'body' => "Пример описания объекта. Замените его настоящим.\n\n- электроснабжение и освещение;\n- 40 точек СКС, серверный шкаф;\n- СКУД на две двери с учётом рабочего времени.",
                'services' => ['elektromontazh-v-ofise-i-magazine', 'sks', 'skud-dlya-ofisov'],
                'photos' => ['ofis.svg', 'dom.svg'],
            ],
            [
                'slug' => 'demo-chastnyy-dom',
                'title' => 'Демо: частный дом, электрика и видеонаблюдение',
                'city' => 'Медведево',
                'year' => 2024,
                'summary' => 'Новая проводка, щиток и четыре камеры по периметру с просмотром с телефона.',
                'body' => 'Пример описания объекта. Замените его настоящим.',
                'services' => ['elektromontazh-v-kvartire-i-dome', 'videonablyudenie-dlya-doma'],
                'photos' => ['dom.svg'],
            ],
        ];
    }
}
