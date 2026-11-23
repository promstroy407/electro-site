<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\Role;
use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Enum;

// Длины полей совпадают с колонками таблицы users.
final class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Имя')
                ->required()
                ->maxLength(100),

            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true),

            Select::make('role')
                ->label('Роль')
                ->options(Role::options())
                ->required()
                ->rule(new Enum(Role::class))
                ->native(false)
                // Свою роль не поменять: так не остаться без администратора.
                ->disabled(fn (?User $record): bool => self::isSelf($record)),

            Toggle::make('is_active')
                ->label('Доступ в админку')
                ->default(true)
                ->disabled(fn (?User $record): bool => self::isSelf($record)),

            TextInput::make('password')
                ->label('Пароль')
                ->helperText('Не короче 12 символов. При редактировании оставьте пустым, чтобы не менять.')
                ->password()
                ->revealable()
                ->required(fn (string $operation): bool => $operation === 'create')
                ->minLength(12)
                ->maxLength(255)
                ->dehydrated(fn (?string $state): bool => filled($state)),
        ]);
    }

    private static function isSelf(?User $record): bool
    {
        return $record !== null && $record->id === auth()->id();
    }
}
