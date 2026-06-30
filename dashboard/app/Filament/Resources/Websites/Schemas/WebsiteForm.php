<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class WebsiteForm
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
        ->label('Website Name')
        ->required()
        ->maxLength(255),

    TextInput::make('domain')
        ->label('Domain')
        ->placeholder('example.com')
        ->helperText('Enter only the domain, e.g. example.com')
        ->required()
        ->unique(ignoreRecord: true)
        ->maxLength(255),

    Toggle::make('status')
        ->label('Active')
        ->default(true),
]);
    }
}
