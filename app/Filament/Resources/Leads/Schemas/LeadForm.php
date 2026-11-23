<?php

declare(strict_types=1);

namespace App\Filament\Resources\Leads\Schemas;

use App\Enums\LeadOrigin;
use App\Enums\LeadStatus;
use App\Models\Lead;
use App\Services\Calculator;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

// Что написал посетитель — только для чтения: заявку не подменить.
// Менеджер меняет статус и свою заметку.
final class LeadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Заявка с сайта')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('Имя')->disabled(),
                    TextInput::make('phone')->label('Телефон')->disabled(),
                    TextInput::make('email')->label('Email')->disabled(),
                    TextInput::make('organization')->label('Организация')->placeholder('не указана')->disabled(),
                    TextInput::make('facility')->label('Объект')->placeholder('не указан')->disabled(),
                    Select::make('service_id')
                        ->label('Услуга')
                        ->relationship('service', 'title')
                        ->placeholder('не выбрана')
                        ->disabled(),
                    Select::make('origin')
                        ->label('Откуда узнал')
                        ->options(LeadOrigin::options())
                        ->placeholder('не указано')
                        ->disabled(),
                    Textarea::make('message')->label('Сообщение')->rows(4)->disabled()->columnSpanFull(),
                    Textarea::make('estimate_summary')
                        ->label('Предварительный расчёт с сайта')
                        ->rows(5)
                        ->disabled()
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Lead $record, Calculator $calculator): ?string => ($estimate = $record?->estimate()) !== null
                            ? implode("\n", $calculator->describe($estimate))
                            : null)
                        ->visible(fn (?Lead $record): bool => $record?->estimate() !== null)
                        ->columnSpanFull(),
                    TextInput::make('source_url')->label('Страница, с которой отправлена')->disabled()->columnSpanFull(),
                ]),

            Section::make('Обработка')
                ->schema([
                    Select::make('status')
                        ->label('Статус')
                        ->options(LeadStatus::options())
                        ->required()
                        ->rule(new Enum(LeadStatus::class))
                        ->native(false),
                    Textarea::make('manager_note')
                        ->label('Заметка менеджера')
                        ->helperText('Видна только в админке.')
                        ->rows(3)
                        ->maxLength(2000),
                ]),
        ]);
    }
}
