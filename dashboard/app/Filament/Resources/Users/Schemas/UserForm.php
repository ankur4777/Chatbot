<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
    ->label('Company')
    ->relationship('company', 'name')
    ->searchable()
    ->preload()
    ->required(),
                TextInput::make('name')
    ->label('Full Name')
    ->required()
    ->maxLength(255),
                TextInput::make('email')
    ->label('Email Address')
    ->email()
    ->unique(ignoreRecord: true)
    ->required()
    ->maxLength(255),
                TextInput::make('password')
    ->label('Password')
    ->password()
    ->revealable()
    ->required(fn (string $operation): bool => $operation === 'create')
    ->dehydrated(fn ($state) => filled($state))
    ->maxLength(255),
                Select::make('role')
    ->label('Role')
    ->options([
        'super_admin' => 'Super Admin',
        'owner' => 'Owner',
        'agent' => 'Agent',
    ])
    ->default('owner')
    ->required(),
                Toggle::make('status')
    ->label('Active')
    ->default(true),
            ]);
    }
}
