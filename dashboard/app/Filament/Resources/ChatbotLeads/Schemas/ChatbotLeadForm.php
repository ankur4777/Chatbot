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
    ->label('Website')
    ->relationship('website', 'name')
    ->searchable()
    ->preload()
    ->required(),
                Select::make('visitor_id')
    ->label('Visitor')
    ->relationship('visitor', 'name')
    ->searchable()
    ->preload()
    ->required(),
                Select::make('conversation_id')
    ->label('Conversation')
    ->relationship('conversation', 'id')
    ->searchable()
    ->preload()
    ->required(),
                TextInput::make('name')
    ->label('Full Name')
    ->required()
    ->maxLength(255),
                TextInput::make('email')
    ->label('Email')
    ->email()
    ->maxLength(255),
                TextInput::make('phone')
    ->label('Phone')
    ->tel()
    ->maxLength(20),
                Textarea::make('notes')
    ->label('Notes')
    ->rows(5)
    ->columnSpanFull(),
            ]);
    }
}
