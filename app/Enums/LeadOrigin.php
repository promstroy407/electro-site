<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Откуда посетитель узнал о компании — ответ из формы заявки.
 * Нужен, чтобы видеть, какая площадка приводит заказы и стоит ли
 * платить ей за рекламу. Порядок — как в выпадающем списке.
 */
enum LeadOrigin: string
{
    case YandexSearch = 'yandex_search';
    case YandexMaps = 'yandex_maps';
    case TwoGis = '2gis';
    case Avito = 'avito';
    case Recommendation = 'recommendation';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::YandexSearch => 'Поиск в Яндексе',
            self::YandexMaps => 'Яндекс Карты',
            self::TwoGis => '2ГИС',
            self::Avito => 'Авито',
            self::Recommendation => 'Посоветовали знакомые',
            self::Other => 'Другое',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $origin) {
            $options[$origin->value] = $origin->label();
        }

        return $options;
    }
}
