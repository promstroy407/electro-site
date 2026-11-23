<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Lead;

/**
 * «Пульс» заявок для админки: открытая вкладка раз в 15 секунд
 * спрашивает, не пришло ли новое, и звенит, если пришло.
 */
final readonly class LeadPulse
{
    public function latestId(): int
    {
        return (int) Lead::query()->max('id');
    }

    /**
     * @return array{latest: int, new: int}
     */
    public function since(int $afterId): array
    {
        return [
            'latest' => $this->latestId(),
            'new' => Lead::query()->where('id', '>', $afterId)->count(),
        ];
    }
}
