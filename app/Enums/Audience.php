<?php

declare(strict_types=1);

namespace App\Enums;

/** Для кого услуга: юрлица, частники или все. */
enum Audience: string
{
    case All = 'all';
    case Business = 'business';
    case Home = 'home';

    public function label(): string
    {
        return match ($this) {
            self::All => 'Всем',
            self::Business => 'Для бизнеса',
            self::Home => 'Для дома',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $audience) {
            $options[$audience->value] = $audience->label();
        }

        return $options;
    }

    /**
     * Какие значения колонки подходят посетителю из этой аудитории.
     *
     * @return list<string>
     */
    public function matchingValues(): array
    {
        return $this === self::All
            ? [self::All->value, self::Business->value, self::Home->value]
            : [self::All->value, $this->value];
    }
}
