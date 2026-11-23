<?php

declare(strict_types=1);

namespace App\Enums;

/** Единица, за которую указана цена в прайсе. */
enum PriceUnit: string
{
    case Piece = 'pcs';
    case Meter = 'm';
    case SquareMeter = 'm2';
    case Point = 'point';
    case Set = 'set';
    case Service = 'service';
    case Hour = 'hour';
    case Month = 'month';

    /**
     * Годится ли цена для «от …» на карточке услуги. Цена за метр кабеля
     * (60 ₽) вводит в заблуждение: работа целиком так не стоит.
     */
    public function suitsPriceFrom(): bool
    {
        return $this !== self::Meter;
    }

    /** Коротко, для строки «от 1 500 ₽ / шт.». */
    public function short(): string
    {
        return match ($this) {
            self::Piece => 'шт.',
            self::Meter => 'м',
            self::SquareMeter => 'м²',
            self::Point => 'точка',
            self::Set => 'компл.',
            self::Service => 'услуга',
            self::Hour => 'час',
            self::Month => 'мес.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $unit) {
            $options[$unit->value] = $unit->short();
        }

        return $options;
    }
}
