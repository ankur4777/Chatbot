<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Repeater;

class ChatbotFlowStepForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               Select::make('chatbot_flow_id')
    ->label('Chatbot Flow')
    ->relationship(
        name: 'flow',
        titleAttribute: 'name',
        modifyQueryUsing: function ($query) {
            $user = auth()->user();

            if ($user && $user->role === 'owner') {
                $query->whereHas('website', function ($websiteQuery) use ($user) {
                    $websiteQuery->where(
                        'company_id',
                        $user->company_id
                    );
                });
            }
        }
    )
    ->searchable()
    ->preload()
    ->required(),

                TextInput::make('step_order')
                    ->label('Step Order')
                    ->required()
                    ->numeric(),

                TextInput::make('step_key')
                    ->label('Step Key')
                    ->default(null),

                Textarea::make('question')
                    ->label('Question')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Toggle::make('is_required')
                    ->label('Required')
                    ->default(true),

                Repeater::make('options')
    ->label('Button Options')
    ->relationship('options')
    ->schema([
        TextInput::make('label')
            ->label('Button Label')
            ->required()
            ->maxLength(255),

        TextInput::make('value')
            ->label('Value')
            ->required()
            ->maxLength(255),

        TextInput::make('sort_order')
            ->label('Sort Order')
            ->numeric()
            ->required()
            ->default(1),
    ])
    ->columns(3)
    ->orderColumn('sort_order')
    ->required()
    ->minItems(1)
    ->defaultItems(1)
    ->addActionLabel('Add Option')
    ->collapsible()
    ->columnSpanFull()
            ]);
    }
}
