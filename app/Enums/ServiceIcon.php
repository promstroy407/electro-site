<?php

declare(strict_types=1);

namespace App\Enums;

/** Иллюстрация направления на сайте. Файлы — resources/views/components/icons/{value}.blade.php. */
enum ServiceIcon: string
{
    case Electrical = 'elektromontazh';
    case LowCurrent = 'slabotochka';
    case Fire = 'pozharka';
    case Video = 'video';
    case Access = 'skud';
    case Security = 'ohrana';
    case General = 'obshchee';

    public function label(): string
    {
        return match ($this) {
            self::Electrical => 'Электрощит',
            self::LowCurrent => 'Розетка СКС',
            self::Fire => 'Пожарный извещатель',
            self::Video => 'Камера',
            self::Access => 'Считыватель и карта',
            self::Security => 'Датчик движения',
            self::General => 'Молния (общая)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $icon) {
            $options[$icon->value] = $icon->label();
        }

        return $options;
    }
}
