<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationSettings;

use App\Filament\Resources\NotificationSettings\Pages\EditNotificationSettings;
use App\Filament\Resources\NotificationSettings\Pages\ListNotificationSettings;
use App\Filament\Resources\NotificationSettings\Schemas\NotificationSettingsForm;
use App\Filament\Resources\NotificationSettings\Tables\NotificationSettingsTable;
use App\Models\NotificationSettings;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

// Одна запись настроек. Доступ — App\Policies\NotificationSettingsPolicy.
final class NotificationSettingsResource extends Resource
{
    protected static ?string $model = NotificationSettings::class;

    protected static ?string $slug = 'notification-settings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static string|UnitEnum|null $navigationGroup = 'Настройки';

    protected static ?string $navigationLabel = 'Уведомления';

    protected static ?string $modelLabel = 'настройки уведомлений';

    protected static ?string $pluralModelLabel = 'уведомления о заявках';

    public static function form(Schema $schema): Schema
    {
        return NotificationSettingsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationSettings::route('/'),
            'edit' => EditNotificationSettings::route('/{record}/edit'),
        ];
    }
}
