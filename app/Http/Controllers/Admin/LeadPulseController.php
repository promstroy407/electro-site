<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Models\Lead;
use App\Services\LeadPulse;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final readonly class LeadPulseController
{
    public function __construct(
        private LeadPulse $pulse,
        private Gate $gate,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $this->gate->authorize('viewAny', Lead::class);

        /** @var array{after: int|string} $input */
        $input = $request->validate([
            'after' => ['required', 'integer', 'min:0', 'max:9223372036854775807'],
        ]);

        return new JsonResponse($this->pulse->since((int) $input['after']));
    }
}
