<?php

declare(strict_types=1);

namespace App\Models;

use App\Observers\PartnerObserver;
use Carbon\CarbonImmutable;
use Database\Factories\PartnerFactory;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Партнёр компании: строительные фирмы, с которыми работаем
 * и обмениваемся ссылками.
 *
 * @property int $id
 * @property string $name
 * @property string|null $url
 * @property string|null $description
 * @property string|null $logo_path
 * @property bool $is_published
 * @property int $sort_order
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 */
#[ObservedBy(PartnerObserver::class)]
class Partner extends Model
{
    /** @use HasFactory<PartnerFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'url',
        'description',
        'logo_path',
        'is_published',
        'sort_order',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'is_published' => true,
        'sort_order' => 0,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /** Адрес сайта без «https://» и «www.» — для подписи ссылки. */
    public function host(): ?string
    {
        if ($this->url === null) {
            return null;
        }

        $host = parse_url($this->url, PHP_URL_HOST);

        return is_string($host) ? (string) preg_replace('/^www\./', '', $host) : null;
    }

    /**
     * Буква вместо логотипа: первая буква названия без формы
     * собственности и кавычек («ООО «Демо Строй»» → «Д»).
     */
    public function initial(): string
    {
        $name = (string) preg_replace('/^(ООО|ОАО|ЗАО|ПАО|АО|ИП)\s+/u', '', trim($this->name));
        $name = trim($name, " \t«»\"'„“");

        return mb_strtoupper(mb_substr($name !== '' ? $name : $this->name, 0, 1));
    }

    /**
     * @param  Builder<Partner>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->where('is_published', true);
    }

    /**
     * @param  Builder<Partner>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('id');
    }
}
