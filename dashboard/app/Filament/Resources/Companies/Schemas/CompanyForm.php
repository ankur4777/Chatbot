<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
    TextInput::make('name')
        ->label('Company Name')
        ->required()
        ->maxLength(255),

    TextInput::make('slug')
        ->required()
        ->unique(ignoreRecord: true)
        ->maxLength(255),

    TextInput::make('owner_name')
    ->label('Owner Name')
    ->maxLength(255),

    TextInput::make('email')
        ->label('Email Address')
        ->email()
        ->maxLength(255),

    TextInput::make('phone')
        ->label('Phone Number')
        ->tel()
        ->maxLength(20),

    TextInput::make('gst_number')
    ->label('GST Number')
    ->maxLength(15),

    
Textarea::make('address')
    ->label('Company Address')
    ->rows(3),
    Toggle::make('status')
        ->label('Active')
        ->default(true),
]);
    }
}
