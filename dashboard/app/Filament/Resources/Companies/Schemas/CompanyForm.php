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
    TextInput::make('legal_name')
        ->label('Legal Name')
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
        ->maxLength(30)
        ->regex('/^(?=(?:\\D*\\d){10,15}\\D*$)\\+?[0-9\\s().-]+$/'),

    TextInput::make('gst_number')
    ->label('GST Number')
    ->maxLength(15),

    
Textarea::make('address')
    ->label('Company Address')
    ->rows(3),

    TextInput::make('county_name')
        ->label('County Name')
        ->maxLength(255),

    TextInput::make('country_code')
        ->label('Country Code')
        ->placeholder('+91, IN, US')
        ->maxLength(8)
        ->regex('/^(?:\\+[1-9][0-9]{0,3}|[A-Za-z]{2,8})$/'),

    TextInput::make('subscription_expires_at')
        ->label('Subscription Expires At')
        ->type('datetime-local')
        ->helperText('Leave empty for no expiry.'),

    TextInput::make('conversation_retention_months')
        ->label('Conversation History Retention')
        ->numeric()
        ->minValue(1)
        ->maxValue(6)
        ->suffix('months')
        ->default(1)
        ->required(),

    Toggle::make('status')
        ->label('Active')
        ->default(true),
]);
    }
}
