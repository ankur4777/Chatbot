<?php

namespace App\Filament\Client\Resources\ChatbotLeads\Schemas;

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