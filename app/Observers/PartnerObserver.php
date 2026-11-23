<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Partner;
use App\Services\PartnerLogos;

/**
 * Не оставлять на диске логотип, который заменили, убрали или удалили
 * вместе с партнёром.
 */
final readonly class PartnerObserver
{
    public function __construct(
        private PartnerLogos $logos,
    ) {}

    public function updated(Partner $partner): void
    {
        $before = $partner->getOriginal('logo_path');

        if ($partner->wasChanged('logo_path') && is_string($before)) {
            $this->logos->delete($before);
        }
    }

    public function deleted(Partner $partner): void
    {
        if ($partner->logo_path !== null) {
            $this->logos->delete($partner->logo_path);
        }
    }
}
