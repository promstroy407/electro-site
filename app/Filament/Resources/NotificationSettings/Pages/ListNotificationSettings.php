<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationSettings\Pages;

use App\Filament\Resources\NotificationSettings\NotificationSettingsResource;
use Filament\Resources\Pages\ListRecords;

final class ListNotificationSettings extends ListRecords
{
    protected static string $resource = NotificationSettingsResource::class;
}
