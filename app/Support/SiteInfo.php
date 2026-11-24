<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Данные компании для публичной части. Собирается из config/site.php,
 * в шаблонах доступен как $site.
 */
final readonly class SiteInfo
{
    public function __construct(
        public string $companyName,
        public string $city,
        public string $cityIn,
        public string $region,
        public string $address,
        public string $phone,
        public string $email,
        public string $workHours,
        public string $telegram = '',
        public string $maxChannel = '',
        public string $vk = '',
        public string $metrikaId = '',
        public string $legalName = '',
        public string $inn = '',
        public string $ogrnip = '',
    ) {}

    /**
     * @param  array<string, mixed>  $config
     */
    public static function fromConfig(array $config): self
    {
        return new self(
            companyName: self::string($config, 'company_name'),
            city: self::string($config, 'city'),
            cityIn: self::string($config, 'city_in'),
            region: self::string($config, 'region'),
            address: self::string($config, 'address'),
            phone: self::string($config, 'phone'),
            email: self::string($config, 'email'),
            workHours: self::string($config, 'work_hours'),
            telegram: self::urlStartingWith(self::string($config, 'telegram'), 'https://t.me/'),
            maxChannel: self::urlStartingWith(self::string($config, 'max_channel'), 'https://max.ru/'),
            vk: self::urlStartingWith(self::string($config, 'vk'), 'https://vk.ru/', 'https://vk.com/'),
            metrikaId: self::digits(self::string($config, 'metrika_id')),
            legalName: self::string($config, 'legal_name'),
            inn: self::exactDigits(self::string($config, 'inn'), 10, 12),
            ogrnip: self::exactDigits(self::string($config, 'ogrnip'), 13, 15),
        );
    }

    /**
     * Наши канал и группа, где выходят посты, — для подвала, статей и
     * микроразметки. Пустые ссылки не попадают.
     *
     * @return list<array{label: string, url: string}>
     */
    public function socialLinks(): array
    {
        $links = [
            ['label' => 'Канал в MAX', 'url' => $this->maxChannel],
            ['label' => 'Группа ВКонтакте', 'url' => $this->vk],
        ];

        return array_values(array_filter($links, fn (array $link): bool => $link['url'] !== ''));
    }

    /** Телефон для ссылки tel: — только цифры и ведущий плюс. */
    public function phoneHref(): string
    {
        return (string) preg_replace('/(?!^\+)[^\d]/', '', $this->phone);
    }

    /**
     * Реквизиты продавца для подвала и политики: «ИП …, ИНН …, ОГРНИП …».
     * Пусто, пока в .env не задано название (SITE_LEGAL_NAME).
     */
    public function requisites(): string
    {
        if ($this->legalName === '') {
            return '';
        }

        $parts = [$this->legalName];

        if ($this->inn !== '') {
            $parts[] = 'ИНН '.$this->inn;
        }

        if ($this->ogrnip !== '') {
            $parts[] = (strlen($this->ogrnip) === 15 ? 'ОГРНИП ' : 'ОГРН ').$this->ogrnip;
        }

        return implode(', ', $parts);
    }

    /** ИНН и ОГРН(ИП) — строго цифры нужной длины, иначе не показываем. */
    private static function exactDigits(string $value, int ...$lengths): string
    {
        $value = trim($value);

        return preg_match('/^\d+$/', $value) === 1 && in_array(strlen($value), $lengths, true) ? $value : '';
    }

    /** Номер счётчика вставляется в скрипт — пропускаем только цифры. */
    private static function digits(string $value): string
    {
        return preg_match('/^\d{1,12}$/', $value) === 1 ? $value : '';
    }

    /**
     * Ссылка на мессенджер идёт в href — пропускаем только адреса
     * самого мессенджера, чужой сайт или javascript: туда не попадут.
     */
    private static function urlStartingWith(string $url, string ...$prefixes): string
    {
        foreach ($prefixes as $prefix) {
            if (str_starts_with($url, $prefix) && strlen($url) > strlen($prefix)) {
                return $url;
            }
        }

        return '';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private static function string(array $config, string $key): string
    {
        $value = $config[$key] ?? '';

        return is_string($value) ? $value : '';
    }
}
