<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Service;
use Carbon\CarbonInterface;

/**
 * Сезонное предложение на главной: зимой — прогрев бетона.
 * Сезон считается по дате, а не хранится: блок сам появляется в
 * октябре и сам пропадает в мае. Текст и цена — из самой услуги
 * (название и краткое описание), чтобы не дублировать их в шаблоне.
 */
final readonly class SeasonalOffer
{
    public const string WINTER_SERVICE_SLUG = 'progrev-betona';

    /** Месяцы, когда бетон греют: с октября по апрель. */
    private const array WINTER_MONTHS = [10, 11, 12, 1, 2, 3, 4];

    public function __construct(
        private ServiceCatalog $catalog,
    ) {}

    public function current(CarbonInterface $today): ?Service
    {
        if (! in_array($today->month, self::WINTER_MONTHS, true)) {
            return null;
        }

        return $this->catalog->findPublished(self::WINTER_SERVICE_SLUG);
    }
}
