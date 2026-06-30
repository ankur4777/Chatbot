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
                Select::make('website_id')
                    ->relationship('website', 'name')
                    ->required(),
                Select::make('visitor_id')
                    ->relationship('visitor', 'name')
                    ->required(),
                Select::make('assigned_agent_id')
                    ->relationship('assignedAgent', 'name')
                    ->default(null),
                Select::make('status')
                    ->options(['active' => 'Active', 'waiting' => 'Waiting', 'closed' => 'Closed'])
                    ->default('active')
                    ->required(),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('ended_at'),
            ]);
    }
}
