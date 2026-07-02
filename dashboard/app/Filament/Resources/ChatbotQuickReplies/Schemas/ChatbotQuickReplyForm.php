<?php

namespace App\Filament\Resources\ChatbotQuickReplies\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

class ChatbotQuickReplyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

    Select::make('website_id')
        ->relationship('website', 'name')
        ->searchable()
        ->preload()
        ->required(),

    TextInput::make('label')
        ->required()
        ->maxLength(255),

    TextInput::make('value')
        ->required()
        ->maxLength(255),

    TextInput::make('icon')
        ->placeholder('bi-airplane, bi-calendar, etc.')
        ->maxLength(100),

    TextInput::make('sort_order')
        ->numeric()
        ->default(0)
        ->required(),

    Toggle::make('is_active')
        ->default(true),

]);
    }
}
