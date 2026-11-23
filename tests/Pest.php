<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

pest()->extend(TestCase::class)
    ->in('Unit');

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function leadPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Иван Петров',
        'phone' => '8 (917) 000-11-22',
        'email' => 'ivan@example.test',
        'message' => 'Нужны камеры на склад',
        'service_id' => null,
        'origin' => '',
        'consent' => '1',
        'company_website' => '',
    ], $overrides);
}
