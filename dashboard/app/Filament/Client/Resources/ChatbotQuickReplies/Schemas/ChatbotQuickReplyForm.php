<?php

namespace App\Filament\Client\Resources\ChatbotQuickReplies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChatbotQuickReplyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('website_id')
                    ->label('Website')
                    ->relationship(
                        name: 'website',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $user = auth()->user();

                            if ($user && $user->role === 'owner') {
                                $query->where(
                                    'company_id',
                                    $user->company_id
                                );
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('label')
                    ->label('Label')
                    ->required()
                    ->maxLength(255),

                TextInput::make('value')
                    ->label('Value')
                    ->required()
                    ->maxLength(255),

                TextInput::make('icon')
                    ->label('Icon')
                    ->placeholder('bi-airplane, bi-calendar, etc.')
                    ->maxLength(100),

                TextInput::make('sort_order')
                    ->label('Sort Order')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}