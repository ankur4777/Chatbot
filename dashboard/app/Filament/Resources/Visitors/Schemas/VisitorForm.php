<?php

namespace App\Filament\Resources\Visitors\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class VisitorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('website_id')
                    ->relationship('website', 'name')
                    ->required(),
                TextInput::make('name')
                    ->default(null),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->default(null),
                TextInput::make('phone')
                    ->tel()
                    ->default(null),
                TextInput::make('session_id')
                    ->required(),
                TextInput::make('ip_address')
                    ->default(null),
                Textarea::make('user_agent')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
