<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Support\Estimate;
use Carbon\CarbonImmutable;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Заявка с сайта.
 *
 * @property int $id
 * @property int|null $service_id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string|null $organization
 * @property string|null $facility
 * @property string|null $message
 * @property LeadStatus $status
 * @property string|null $manager_note
 * @property string|null $source_url
 * @property LeadOrigin|null $origin
 * @property int|null $estimate_area_m2
 * @property array<array-key, mixed>|null $estimate_items
 * @property CarbonImmutable $consent_at
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Service|null $service
 */
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'service_id',
        'name',
        'phone',
        'email',
        'organization',
        'facility',
        'message',
        'status',
        'manager_note',
        'source_url',
        'origin',
        'estimate_area_m2',
        'estimate_items',
        'consent_at',
    ];

    /** @var array<string, mixed> */
    protected $attributes = [
        'status' => 'new',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'origin' => LeadOrigin::class,
            'estimate_area_m2' => 'integer',
            'estimate_items' => 'array',
            'consent_at' => 'datetime',
        ];
    }

    /** Предварительный расчёт, если заявка пришла из калькулятора. */
    public function estimate(): ?Estimate
    {
        if ($this->estimate_area_m2 === null || $this->estimate_items === null) {
            return null;
        }

        return Estimate::fromStored($this->estimate_area_m2, $this->estimate_items);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
