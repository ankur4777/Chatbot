<?php

namespace App\Filament\Client\Resources\ChatbotFlows\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ChatbotFlowForm
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
        modifyQueryUsing: function ($query, $record) {

            $user = auth()->user();

            if ($user && $user->role === 'owner') {
                $query->where(
                    'company_id',
                    $user->company_id
                );
            }

            $query->where(function ($query) use ($record) {

                $query->whereDoesntHave('chatbotFlow');

                if ($record?->website_id) {
                    $query->orWhere(
                        'id',
                        $record->website_id
                    );
                }
            });
        }
    )
    ->searchable()
    ->preload()
    ->required(),

                TextInput::make('name')
                    ->label('Flow Name')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}