<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required()
                    ->maxLength(30)
                    ->regex('/^\+?[0-9\s\-()]{7,30}$/'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),

                Select::make('availability_status')
                    ->label('Availability')
                    ->options([
                        'online' => 'Online',
                        'away' => 'Away',
                        'offline' => 'Offline',
                    ])
                    ->default('offline')
                    ->required(),

                Select::make('assignedWebsites')
                    ->label('Websites')
                    ->relationship(
                        name: 'assignedWebsites',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) =>
                            $query->where(
                                'company_id',
                                auth()->user()->company_id
                            )
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->helperText('Select which websites this agent can handle.'),
            ]);
    }
}
