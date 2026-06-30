<?php

namespace App\Filament\Resources\ChatConversations\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class ChatConversationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('assigned_agent_id')
                    ->label('Assigned Agent')
                    ->relationship('assignedAgent', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'waiting' => 'Waiting',
                        'closed' => 'Closed',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}

