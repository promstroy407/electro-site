<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\LeadOrigin;
use App\Services\LeadSubmission;
use App\Services\PhoneNormalizer;
use App\Support\Estimate;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Заявка с публичной формы. Длины полей совпадают с колонками leads.
 */
final class StoreLeadRequest extends FormRequest
{
    /** Скрытое поле-ловушка: человек его не видит, бот заполняет. */
    public const HONEYPOT = 'company_website';

    /** Форма калькулятора отличается от обычной заявки полем площади. */
    public const ESTIMATE_AREA = 'estimate_area';

    public const ESTIMATE_RATES = 'estimate_rates';

    /** Ошибки калькулятора — отдельно, чтобы не появлялись у обычной формы. */
    public const ESTIMATE_ERRORS = 'estimate';

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Laravel вызывает rules() через контейнер, поэтому зависимости
     * приходят аргументами.
     *
     * @return array<string, mixed>
     */
    public function rules(PhoneNormalizer $phones): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'phone' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9\s()+\-]+$/',
                function (string $attribute, mixed $value, Closure $fail) use ($phones): void {
                    if (is_string($value) && ! $phones->isPlausible($value)) {
                        $fail('Проверьте номер телефона: в нём должно быть от 10 до 15 цифр.');
                    }
                },
            ],
            'email' => ['nullable', 'string', 'email:rfc', 'max:255'],
            'organization' => ['nullable', 'string', 'max:150'],
            'facility' => ['nullable', 'string', 'max:200'],
            'message' => ['nullable', 'string', 'max:2000'],
            'service_id' => [
                'nullable',
                'integer',
                Rule::exists('services', 'id')->where('is_published', true),
            ],
            'origin' => ['nullable', 'string', Rule::enum(LeadOrigin::class)],
            'consent' => ['accepted'],
            self::HONEYPOT => ['prohibited'],
            self::ESTIMATE_AREA => [
                'nullable',
                'required_with:'.self::ESTIMATE_RATES,
                'integer',
                'min:'.Estimate::MIN_AREA,
                'max:'.Estimate::MAX_AREA,
            ],
            self::ESTIMATE_RATES => ['nullable', 'required_with:'.self::ESTIMATE_AREA, 'array', 'min:1', 'max:20'],
            self::ESTIMATE_RATES.'.*' => [
                'integer',
                'distinct',
                Rule::exists('calculator_rates', 'id')->where('is_published', true),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Имя',
            'phone' => 'Телефон',
            'email' => 'Email',
            'organization' => 'Организация',
            'facility' => 'Объект',
            'message' => 'Сообщение',
            'service_id' => 'Услуга',
            'origin' => 'Откуда вы о нас узнали',
            'consent' => 'Согласие на обработку данных',
            self::ESTIMATE_AREA => 'Площадь',
            self::ESTIMATE_RATES => 'Работы',
            self::ESTIMATE_RATES.'.*' => 'Работы',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'В телефоне допустимы только цифры, пробелы, скобки, дефис и плюс.',
            'consent.accepted' => 'Без согласия на обработку персональных данных мы не можем принять заявку.',
            self::ESTIMATE_RATES.'.required_with' => 'Отметьте хотя бы одну работу.',
            self::ESTIMATE_AREA.'.required_with' => 'Укажите площадь объекта.',
        ];
    }

    public function submission(): LeadSubmission
    {
        $email = $this->string('email')->trim()->toString();
        $message = $this->string('message')->trim()->toString();
        $organization = $this->string('organization')->trim()->toString();
        $facility = $this->string('facility')->trim()->toString();
        $serviceId = $this->filled('service_id') ? $this->integer('service_id') : null;
        $referer = $this->headers->get('referer');

        return new LeadSubmission(
            name: $this->string('name')->trim()->toString(),
            phone: $this->string('phone')->toString(),
            email: $email !== '' ? $email : null,
            message: $message !== '' ? $message : null,
            serviceId: $serviceId,
            sourceUrl: is_string($referer) && $referer !== '' ? mb_substr($referer, 0, 2048) : null,
            origin: $this->filled('origin') ? LeadOrigin::from($this->string('origin')->toString()) : null,
            estimateAreaM2: $this->isEstimate() ? $this->integer(self::ESTIMATE_AREA) : null,
            estimateRateIds: $this->isEstimate() ? $this->estimateRateIds() : [],
            organization: $organization !== '' ? $organization : null,
            facility: $facility !== '' ? $facility : null,
        );
    }

    /** Заявка пришла из калькулятора «Предварительный расчёт». */
    public function isEstimate(): bool
    {
        return $this->has(self::ESTIMATE_AREA);
    }

    /**
     * @return list<int>
     */
    private function estimateRateIds(): array
    {
        $ids = $this->input(self::ESTIMATE_RATES, []);

        if (! is_array($ids)) {
            return [];
        }

        $numeric = array_filter($ids, fn (mixed $id): bool => is_numeric($id));

        return array_values(array_map(fn (mixed $id): int => (int) $id, $numeric));
    }

    /** После ошибки вернуть посетителя к форме, а не в начало страницы. */
    protected function getRedirectUrl(): string
    {
        return parent::getRedirectUrl().($this->isEstimate() ? '#raschet' : '#zayavka');
    }

    protected function prepareForValidation(): void
    {
        if ($this->isEstimate()) {
            $this->errorBag = self::ESTIMATE_ERRORS;
        }
    }
}
