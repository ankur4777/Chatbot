<?php

namespace App\Filament\Resources\Companies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Textarea;

class CompanyForm
{
    private const COUNTRIES = [
        'IN' => ['India (+91)', [10]],
        'CA' => ['Canada (+1)', [10]],
        'US' => ['United States (+1)', [10]],
        'GB' => ['United Kingdom (+44)', [7, 9, 10]],
        'AU' => ['Australia (+61)', [9]],
        'AE' => ['United Arab Emirates (+971)', [8, 9]],
        'SG' => ['Singapore (+65)', [8]],
        'NZ' => ['New Zealand (+64)', [5, 6, 7, 8, 9, 10]],
        'DE' => ['Germany (+49)', [5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]],
        'FR' => ['France (+33)', [9]],
    ];

    private static function phoneLengths(?string $country): array
    {
        return self::COUNTRIES[$country][1] ?? [];
    }

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

    TextInput::make('email')
        ->label('Email Address')
        ->email()
        ->maxLength(255),

    Select::make('country_code')
        ->label('Country')
        ->options(array_map(fn (array $country): string => $country[0], self::COUNTRIES))
        ->searchable()
        ->required()
        ->live()
        ->afterStateUpdated(fn (Set $set) => $set('phone', null)),

    TextInput::make('phone')
        ->label('Phone Number')
        ->inputMode('numeric')
        ->mask(fn (Get $get): string => str_repeat('9', max(self::phoneLengths($get('country_code')) ?: [15])))
        ->disabled(fn (Get $get): bool => self::phoneLengths($get('country_code')) === [])
        ->maxLength(fn (Get $get): int => max(self::phoneLengths($get('country_code')) ?: [15]))
        ->regex(fn (Get $get): string => '/\\A(?:' . implode('|', array_map(
            fn (int $length): string => '[0-9]{' . $length . '}',
            self::phoneLengths($get('country_code')) ?: [0]
        )) . ')\\z/')
        ->validationMessages(['regex' => 'Enter a numeric phone number with the correct digit length for the selected country.']),

    TextInput::make('gst_number')
    ->label('GST Number')
    ->maxLength(15),

    
Textarea::make('address')
    ->label('Company Address')
    ->rows(3),

    TextInput::make('county_name')
        ->label('County Name')
        ->maxLength(255),

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
