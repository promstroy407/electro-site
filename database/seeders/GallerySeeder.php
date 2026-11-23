<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PhotoCategory;
use App\Models\GalleryPhoto;
use App\Services\GalleryPhotos;
use Illuminate\Contracts\Filesystem\Factory as Filesystems;
use Illuminate\Database\Seeder;

/**
 * Настоящие фото с объектов владельца (отобраны им самим, ноябрь 2026).
 * Подписи — по тому, что видно на снимке; города и названия объектов
 * владелец не называл, поэтому только тип объекта.
 * Заливается и на проде (через ContentSeeder), только в пустую галерею.
 */
final class GallerySeeder extends Seeder
{
    private const SOURCE = __DIR__.'/gallery-photos';

    /** Эти фото — первыми: они же идут на главную. */
    private const FEATURED = [
        '45-sborka-nku-v-shchitovoy.jpg',
        '13-osveshchenie-torgovogo-tsentra.jpg',
        '01-vvod-kabeley-v-zdanie.jpg',
        '52-shkaf-upravleniya-oborudovaniem-tsekha.jpg',
        '38-shkaf-du-kdu-pozharnaya-avtomatika.jpg',
        '35-kabelnye-lotki-v-tsekhe.jpg',
    ];

    public function run(Filesystems $filesystems): void
    {
        $disk = $filesystems->disk(GalleryPhotos::DISK);

        foreach ($this->photos() as $index => [$file, $caption, $category, $place]) {
            $path = GalleryPhotos::DIRECTORY.'/'.$file;
            if (! $disk->exists($path)) {
                $disk->put($path, (string) file_get_contents(self::SOURCE.'/'.$file));
            }

            $featured = array_search($file, self::FEATURED, true);

            GalleryPhoto::query()->firstOrCreate(['path' => $path], [
                'caption' => $caption,
                'category' => $category,
                'place' => $place,
                'is_published' => true,
                'sort_order' => $featured === false ? 100 + $index * 10 : $featured * 10,
            ]);
        }
    }

    /**
     * @return list<array{0: string, 1: string, 2: PhotoCategory, 3: string|null}>
     */
    private function photos(): array
    {
        return [
            ['01-vvod-kabeley-v-zdanie.jpg', 'Ввод кабельных линий в здание: гофротруба и трубы ПНД в траншее', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['02-obratnaya-zasypka-transhei.jpg', 'Обратная засыпка траншеи песком после прокладки', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['03-kabelnaya-liniya-v-pnd-trubakh.jpg', 'Кабельная линия в трубах ПНД на строящемся объекте', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['04-vvod-v-kabelnyy-kolodets.jpg', 'Прокладка гофротруб к кабельному колодцу', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['05-chernovoy-montazh-kvartira.jpg', 'Черновой электромонтаж в квартире: трассы по потолку, распаечные коробки', PhotoCategory::Cables, 'Квартира'],
            ['06-shchity-ucheta-v-elektroshchitovoy.jpg', 'Шкафы управления дымоудалением, два ряда', PhotoCategory::Fire, 'Торговый центр'],
            ['07-pomeshchenie-ozds-koridor.jpg', 'Коридор у помещения противопожарного оборудования, красная полоса — защита от грызунов', PhotoCategory::Fire, 'Торговый центр'],
            ['08-vkhod-v-elektroshchitovuyu.jpg', 'Вход в электрощитовую, распределительные щиты', PhotoCategory::Switchboards, 'Торговый центр'],
            ['09-raspredelitelnye-shchity-s-zazemleniem.jpg', 'Распределительные щиты, подключение заземления', PhotoCategory::Switchboards, 'Торговый центр'],
            ['10-elektroshchitovaya-shchity-i-vru.jpg', 'Электрощитовая: распределительные щиты и ВРУ', PhotoCategory::Switchboards, 'Торговый центр'],
            ['11-vru-s-avr.jpg', 'ВРУ с автоматическим вводом резерва (АВР)', PhotoCategory::Switchboards, 'Торговый центр'],
            ['12-vru-dva-vvoda.jpg', 'ВРУ на два ввода: лучи А и Б', PhotoCategory::Switchboards, 'Торговый центр'],
            ['13-osveshchenie-torgovogo-tsentra.jpg', 'Освещение торгового зала: подвесные светильники', PhotoCategory::Lighting, 'Торговый центр'],
            ['14-osveshchenie-galerei.jpg', 'Освещение галереи', PhotoCategory::Lighting, 'Торговый центр'],
            ['15-kabelnye-trassy-po-perekrytiyu.jpg', 'Кабельные трассы по перекрытию', PhotoCategory::Cables, 'Торговый центр'],
            ['16-podvesnye-svetilniki-zal.jpg', 'Подвесные светильники в зале', PhotoCategory::Lighting, 'Торговый центр'],
            ['17-osveshchenie-prokhoda.jpg', 'Освещение прохода вдоль кирпичных колонн', PhotoCategory::Lighting, 'Торговый центр'],
            ['18-shchitovaya-rp.jpg', 'Щитовая: распределительные щиты на раме', PhotoCategory::Switchboards, 'Торговый центр'],
            ['19-vvod-kabeley-v-shchitovuyu.jpg', 'Ввод силовых кабелей в электрощитовую по лоткам', PhotoCategory::Cables, 'Торговый центр'],
            ['20-shkaf-upravleniya-liftom.jpg', 'Шкаф управления лифтом', PhotoCategory::Automation, 'Торговый центр'],
            ['21-shkaf-upravleniya-liftom-avtomatika.jpg', 'Шкаф управления лифтом: пульт и автоматика', PhotoCategory::Automation, 'Торговый центр'],
            ['22-zal-pered-montazhom-osveshcheniya.jpg', 'Торговый зал до монтажа: кабельные выпуски под оборудование', PhotoCategory::Lighting, 'Торговый центр'],
            ['23-osveshchenie-torgovoy-galerei.jpg', 'Освещение торговой галереи', PhotoCategory::Lighting, 'Торговый центр'],
            ['24-montazh-svetilnikov-na-vysote.jpg', 'Монтаж светильников на высоте с вышки-туры', PhotoCategory::Lighting, 'Торговый центр'],
            ['25-svetilniki-pod-kryshey-zala.jpg', 'Светильники под кровлей зала', PhotoCategory::Lighting, 'Торговый центр'],
            ['26-kabelnyy-vvod-v-vru.jpg', 'Ввод силовых кабелей в ВРУ', PhotoCategory::Switchboards, 'Торговый центр'],
            ['27-shkaf-avtomatiki-kontroller.jpg', 'Шкаф автоматики: контроллер и сетевой коммутатор', PhotoCategory::Automation, 'Торговый центр'],
            ['28-tablo-vykhod-i-ipr.jpg', 'Табло «Выход» и ручной пожарный извещатель', PhotoCategory::Fire, 'Торговый центр'],
            ['29-moduli-rubezh-mdu-iz.jpg', 'Модули пожарной автоматики: МДУ-1, ИЗ-1, РМ-4К', PhotoCategory::Fire, 'Торговый центр'],
            ['30-signalnaya-lenta-nad-kabelem.jpg', 'Сигнальная лента «Осторожно, кабель» над линией', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['31-almaznoe-burenie-kabelnye-prokhody.jpg', 'Алмазное бурение отверстий под кабельные проходы', PhotoCategory::Cables, null],
            ['32-tsekh-sortirovki-osveshchenie.jpg', 'Цех сортировки: освещение и питание линии', PhotoCategory::Lighting, 'Цех сортировки отходов'],
            ['33-transheya-pod-kabel.jpg', 'Контур заземления в траншее', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['34-stalnye-truby-perekhod-zazemlenie.jpg', 'Стальные трубы для перехода кабеля и полоса заземления', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['35-kabelnye-lotki-v-tsekhe.jpg', 'Кабельные лотки лестничного типа в цехе', PhotoCategory::Cables, 'Цех сортировки отходов'],
            ['36-lotki-nad-konveyerom.jpg', 'Кабельные лотки над конвейерной линией', PhotoCategory::Cables, 'Цех сортировки отходов'],
            ['37-osveshchenie-i-slabotochka-v-tts.jpg', 'Освещение и слаботочные сети в торговой галерее', PhotoCategory::Lighting, null],
            ['38-shkaf-du-kdu-pozharnaya-avtomatika.jpg', 'Шкаф дымоудаления: кнопки ДУ и КДУ, модули МДУ', PhotoCategory::Fire, null],
            ['39-kabel-v-transhee-s-lentoy.jpg', 'Кабель в траншее под сигнальной лентой', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['40-kabelnaya-mufta-vvod-10kv.jpg', 'Ввод кабеля в ячейку РУ, концевая муфта', PhotoCategory::Switchboards, null],
            ['41-podklyuchenie-k-shinam-nku.jpg', 'Подключение кабелей к шинам НКУ', PhotoCategory::Switchboards, null],
            ['42-podklyuchenie-kabeley-v-nku.jpg', 'Разводка кабелей в НКУ', PhotoCategory::Switchboards, null],
            ['43-montazh-kabeley-na-shiny.jpg', 'Монтаж кабелей на шины', PhotoCategory::Switchboards, null],
            ['44-ru-10kv-kru.jpg', 'Распределительное устройство 10 кВ RM6', PhotoCategory::Switchboards, null],
            ['45-sborka-nku-v-shchitovoy.jpg', 'Сборка НКУ в щитовой', PhotoCategory::Switchboards, null],
            ['46-montazh-osveshcheniya-na-lestnitse.jpg', 'Монтаж освещения у эвакуационного выхода', PhotoCategory::Lighting, null],
            ['47-lotki-i-kabeli-v-tsekhe.jpg', 'Кабельные лотки и разводка в цехе', PhotoCategory::Cables, 'Цех сортировки отходов'],
            ['48-vertikalnye-kabelnye-lotki.jpg', 'Вертикальные кабельные лотки, кабели на стяжках', PhotoCategory::Cables, null],
            ['49-gilzy-dlya-prokhoda-kabeley.jpg', 'Стальные гильзы для прохода кабелей под фундаментом', PhotoCategory::Cables, 'Наружные кабельные сети'],
            ['50-tsekh-sortirovki-obshchiy-vid.jpg', 'Цех сортировки: общий вид', PhotoCategory::Lighting, 'Цех сортировки отходов'],
            ['51-montazh-svetilnika-pod-perekrytiem.jpg', 'Монтаж светильника под перекрытием', PhotoCategory::Lighting, null],
            ['52-shkaf-upravleniya-oborudovaniem-tsekha.jpg', 'Шкаф управления оборудованием цеха', PhotoCategory::Automation, 'Цех сортировки отходов'],
        ];
    }
}
