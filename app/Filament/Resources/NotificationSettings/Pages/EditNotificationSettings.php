<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationSettings\Pages;

use App\Filament\Resources\NotificationSettings\NotificationSettingsResource;
use App\Models\NotificationSettings;
use App\Services\LeadNotifications\LeadNotifier;
use App\Services\LeadNotifications\TelegramBot;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;
use Throwable;

final class EditNotificationSettings extends EditRecord
{
    protected static string $resource = NotificationSettingsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('findTelegramChat')
                ->label('Найти мой чат')
                ->icon(Heroicon::OutlinedMagnifyingGlass)
                ->color('gray')
                ->action(function (TelegramBot $bot): void {
                    $this->findTelegramChat($bot);
                }),

            Action::make('sendTest')
                ->label('Отправить тестовое уведомление')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->action(function (LeadNotifier $notifier): void {
                    $this->sendTest($notifier);
                }),
        ];
    }

    private function findTelegramChat(TelegramBot $bot): void
    {
        try {
            $chat = $bot->latestChat();
        } catch (Throwable $e) {
            Notification::make()->title('Не удалось обратиться к Telegram')->body($e->getMessage())->danger()->send();

            return;
        }

        if ($chat === null) {
            Notification::make()
                ->title('Чат не найден')
                ->body('Напишите своему боту в Telegram любое сообщение и нажмите кнопку ещё раз.')
                ->warning()
                ->send();

            return;
        }

        /** @var NotificationSettings $settings */
        $settings = $this->getRecord();
        $settings->update([
            'telegram_chat_id' => $chat['id'],
            'telegram_enabled' => true,
        ]);
        $this->refreshFormData(['telegram_chat_id', 'telegram_enabled']);

        Notification::make()
            ->title('Чат найден и сохранён: '.$chat['title'])
            ->body('Теперь нажмите «Отправить тестовое уведомление».')
            ->success()
            ->send();
    }

    private function sendTest(LeadNotifier $notifier): void
    {
        $results = $notifier->sendTest();

        if ($results === []) {
            Notification::make()
                ->title('Ни один канал не включён')
                ->body('Тест проверяет сохранённые настройки: сначала сохраните форму.')
                ->warning()
                ->send();

            return;
        }

        foreach ($results as $channel => $delivered) {
            $notification = Notification::make();
            if ($delivered) {
                $notification->title($channel.': отправлено')->success();
            } else {
                $notification->title($channel.': не отправлено')->body('Подробности — в журнале storage/logs.')->danger();
            }
            $notification->send();
        }
    }
}
