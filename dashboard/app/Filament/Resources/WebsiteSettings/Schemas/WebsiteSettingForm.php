<?php

namespace App\Filament\Resources\WebsiteSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;

class WebsiteSettingForm
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
                TextInput::make('chatbot_name')
    ->label('Chatbot Name')
    ->required()
    ->maxLength(100)
    ->default('AI Assistant'),
                Textarea::make('welcome_message')
                    ->rows(3)
                    ->default('Hello 👋 How can I help you today?'),
                TextInput::make('placeholder')
    ->label('Input Placeholder')
    ->required()
    ->maxLength(255)
    ->default('Type your message...'),

                TextInput::make('temperature')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(2)
                    ->step(0.1)
                    ->default(0.7)
                    ->required(),
                TextInput::make('primary_color')
                    ->required()
                    ->default('#2563eb'),
                Select::make('position')
                    ->options(['left' => 'Left', 'right' => 'Right'])
                    ->default('right')
                    ->required(),
                Toggle::make('enable_chatbot')
                    ->label('Enable Chatbot')
                    ->default(true),
                Toggle::make('enable_live_chat')
                    ->label('Enable Live Chat')
                    ->default(true),
                Toggle::make('show_connect_agent')
                    ->label('Show Connect to Agent')
                    ->default(true),
            ]);
    }
}
