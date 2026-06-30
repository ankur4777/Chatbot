<?php

namespace App\Filament\Resources\WebsiteSettings\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WebsiteSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('website_id')
                    ->relationship('website', 'name')
                    ->required(),
                TextInput::make('chatbot_name')
                    ->required()
                    ->default('AI Assistant'),
                TextInput::make('welcome_message')
                    ->default(null),
                TextInput::make('placeholder')
                    ->required()
                    ->default('Type your message...'),
                TextInput::make('language')
                    ->required()
                    ->default('en'),
                TextInput::make('model')
                    ->required()
                    ->default('qwen'),
                TextInput::make('temperature')
                    ->required()
                    ->numeric()
                    ->default(0.7),
                TextInput::make('primary_color')
                    ->required()
                    ->default('#2563eb'),
                Select::make('position')
                    ->options(['left' => 'Left', 'right' => 'Right'])
                    ->default('right')
                    ->required(),
                Toggle::make('enable_chatbot')
                    ->required(),
                Toggle::make('enable_live_chat')
                    ->required(),
                Toggle::make('show_connect_agent')
                    ->required(),
            ]);
    }
}
