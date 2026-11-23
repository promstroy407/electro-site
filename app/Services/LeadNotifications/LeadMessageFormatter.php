<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use App\Models\Lead;
use App\Services\Calculator;
use App\Support\SiteInfo;
use Illuminate\Contracts\Routing\UrlGenerator;

/** Собирает текст уведомления о заявке. Простой текст: без разметки, без экранирования. */
final readonly class LeadMessageFormatter
{
    public function __construct(
        private UrlGenerator $urls,
        private SiteInfo $site,
        private Calculator $calculator,
    ) {}

    public function forLead(Lead $lead): LeadMessage
    {
        $lead->loadMissing('service');

        $lines = [
            'Новая заявка №'.$lead->id,
            '',
            'Имя: '.$lead->name,
            'Телефон: '.$lead->phone,
        ];

        if ($lead->email !== null) {
            $lines[] = 'Email: '.$lead->email;
        }

        if ($lead->organization !== null) {
            $lines[] = 'Организация: '.$lead->organization;
        }

        if ($lead->facility !== null) {
            $lines[] = 'Объект: '.$lead->facility;
        }

        $lines[] = 'Услуга: '.($lead->service !== null ? $lead->service->title : 'не выбрана');
        $lines[] = 'Откуда узнал: '.($lead->origin !== null ? $lead->origin->label() : 'не указано');

        $estimate = $lead->estimate();

        if ($estimate !== null) {
            $lines[] = '';
            $lines[] = 'Предварительный расчёт с сайта:';
            array_push($lines, ...$this->calculator->describe($estimate));
        }

        if ($lead->message !== null) {
            $lines[] = '';
            $lines[] = 'Сообщение:';
            $lines[] = $lead->message;
        }

        if ($lead->source_url !== null) {
            $lines[] = '';
            $lines[] = 'Страница: '.$lead->source_url;
        }

        $lines[] = '';
        $lines[] = 'Открыть в админке: '.$this->urls->to('/admin/leads/'.$lead->id);

        return new LeadMessage(
            subject: 'Новая заявка: '.$lead->name.', '.$lead->phone,
            body: implode("\n", $lines),
        );
    }

    public function test(): LeadMessage
    {
        return new LeadMessage(
            subject: 'Проверка уведомлений — '.$this->site->companyName,
            body: "Это тестовое сообщение с сайта.\nЕсли вы его читаете, уведомления о заявках будут приходить сюда.",
        );
    }
}
