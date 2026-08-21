<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

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

                Select::make('input_type')
                    ->label('Input Type')
                    ->options([
                        'buttons' => 'Buttons',
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'number' => 'Number',
                        'email' => 'Email',
                        'phone' => 'Phone',
                        'date' => 'Date',
                        'select' => 'Select',
                        'radio' => 'Radio',
                        'checkbox' => 'Checkbox',
                    ])
                    ->searchable()
                    ->required(),

                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->default(null),

                Toggle::make('is_required')
                    ->label('Required')
                    ->default(true),
            ]);
    }
}