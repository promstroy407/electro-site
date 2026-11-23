<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\LeadOrigin;

/** Проверенные данные заявки из формы на сайте. */
final readonly class LeadSubmission
{
    /**
     * @param  list<int>  $estimateRateIds  отмеченные работы калькулятора
     */
    public function __construct(
        public string $name,
        public string $phone,
        public ?string $email,
        public ?string $message,
        public ?int $serviceId,
        public ?string $sourceUrl,
        public ?LeadOrigin $origin = null,
        public ?int $estimateAreaM2 = null,
        public array $estimateRateIds = [],
        public ?string $organization = null,
        public ?string $facility = null,
    ) {}
}
