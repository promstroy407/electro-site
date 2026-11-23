<?php

declare(strict_types=1);

namespace App\Enums;

/** Раздел фотогалереи: по нему посетитель фильтрует фото. */
enum PhotoCategory: string
{
    case Cables = 'kabel';
    case Switchboards = 'shchity';
    case Automation = 'avtomatika';
    case Lighting = 'svet';
    case Fire = 'pozhar';

    public function label(): string
    {
        return match ($this) {
            self::Cables => 'Кабельные линии и трассы',
            self::Switchboards => 'Электрощитовые, ВРУ и РУ',
            self::Automation => 'Шкафы управления',
            self::Lighting => 'Освещение',
            self::Fire => 'Пожарная автоматика',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $category) {
            $options[$category->value] = $category->label();
        }

        return $options;
    }
}
