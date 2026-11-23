<?php

declare(strict_types=1);

namespace App\Services\LeadNotifications;

use App\Models\Lead;
use App\Services\NotificationSettingsStore;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Рассылает уведомление по всем включённым каналам. Сбой одного канала
 * не мешает остальным и не повторяется: повтор задвоил бы сообщения
 * в каналах, которые уже сработали.
 */
final readonly class LeadNotifier
{
    public function __construct(
        private NotificationSettingsStore $settings,
        private LeadMessageFormatter $formatter,
        private MailLeadChannel $mail,
        private TelegramLeadChannel $telegram,
        private LoggerInterface $logger,
    ) {}

    /**
     * @return array<string, bool> канал => доставлено ли
     */
    public function notifyAboutLead(Lead $lead): array
    {
        return $this->deliver($this->formatter->forLead($lead), 'lead '.$lead->id);
    }

    /**
     * @return array<string, bool> канал => доставлено ли
     */
    public function sendTest(): array
    {
        return $this->deliver($this->formatter->test(), 'test');
    }

    /**
     * @return list<LeadChannel>
     */
    private function channels(): array
    {
        return [$this->mail, $this->telegram];
    }

    /**
     * @return array<string, bool>
     */
    private function deliver(LeadMessage $message, string $context): array
    {
        $settings = $this->settings->current();
        $results = [];

        foreach ($this->channels() as $channel) {
            if (! $channel->isEnabled($settings)) {
                continue;
            }

            try {
                $channel->send($settings, $message);
                $results[$channel->label()] = true;
            } catch (Throwable $e) {
                $results[$channel->label()] = false;
                $this->logger->error('Уведомление о заявке не отправлено', [
                    'channel' => $channel->label(),
                    'context' => $context,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }
}
