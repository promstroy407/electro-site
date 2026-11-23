<?php

declare(strict_types=1);

namespace App\Services\TenderRadar;

use App\Models\Lead;
use App\Services\Calculator;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Http\Client\Factory;
use RuntimeException;
use Throwable;

/**
 * Передаёт заявку с сайта в «Тендер-Радар» (tender.es12.ru), где ведётся
 * общая воронка сделок. Повтор безопасен: «Тендер-Радар» узнаёт заявку
 * по external_id и второй сделки не создаёт.
 *
 * Адрес и токен — только из .env (TENDER_RADAR_LEADS_URL,
 * TENDER_RADAR_LEADS_TOKEN). Пусто — передача выключена.
 */
final readonly class TenderRadarExporter
{
    private const ESTIMATE_MAX = 500;

    public function __construct(
        private Factory $http,
        private Repository $config,
        private Calculator $calculator,
    ) {}

    public function isEnabled(): bool
    {
        return $this->url() !== '' && $this->token() !== '';
    }

    /**
     * @return int номер сделки в «Тендер-Радаре»
     *
     * @throws RuntimeException при любой ошибке; токен в тексте заменён на ***
     */
    public function export(Lead $lead): int
    {
        $url = $this->url();
        $token = $this->token();

        if ($url === '' || $token === '') {
            throw new RuntimeException('Тендер-Радар: не заданы TENDER_RADAR_LEADS_URL и TENDER_RADAR_LEADS_TOKEN в .env');
        }

        if (! str_starts_with($url, 'https://') || parse_url($url, PHP_URL_HOST) === null) {
            throw new RuntimeException('TENDER_RADAR_LEADS_URL в .env записан неверно: нужен адрес https://…');
        }

        try {
            $response = $this->http
                ->timeout(10)
                ->withToken($token)
                ->acceptJson()
                ->asJson()
                ->post($url, $this->payload($lead))
                ->throw();
        } catch (Throwable $e) {
            throw new RuntimeException('Тендер-Радар: '.str_replace($token, '***', $e->getMessage()));
        }

        $dealId = $response->json('deal_id');

        if (! is_int($dealId)) {
            throw new RuntimeException('Тендер-Радар: в ответе нет deal_id (HTTP '.$response->status().')');
        }

        return $dealId;
    }

    /**
     * Поля — как договорились с «Тендер-Радаром» (POST /api/leads).
     *
     * @return array<string, string|null>
     */
    public function payload(Lead $lead): array
    {
        $lead->loadMissing('service');
        $estimate = $lead->estimate();

        return [
            'external_id' => 'es12-'.$lead->id,
            'received_at' => $lead->created_at?->toIso8601String(),
            'name' => $lead->name,
            'phone' => $lead->phone,
            'email' => $lead->email,
            'organization' => $lead->organization,
            'facility' => $lead->facility,
            'service' => $lead->service?->title,
            'origin' => $lead->origin?->label(),
            'message' => $lead->message,
            'estimate' => $estimate !== null
                ? mb_substr(implode('; ', $this->calculator->describe($estimate)), 0, self::ESTIMATE_MAX)
                : null,
            'source_url' => $lead->source_url,
        ];
    }

    private function url(): string
    {
        $url = $this->config->get('services.tender_radar.leads_url');

        return is_string($url) ? trim($url) : '';
    }

    private function token(): string
    {
        $token = $this->config->get('services.tender_radar.leads_token');

        return is_string($token) ? trim($token) : '';
    }
}
