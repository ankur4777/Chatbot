<?php

namespace App\Filament\Resources\ChatbotLeads\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class ChatbotLeadForm
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
                Select::make('conversation_id')
                    ->relationship('conversation', 'id')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                Textarea::make('notes')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
