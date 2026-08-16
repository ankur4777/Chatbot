<?php

namespace App\Filament\Resources\ChatbotFlowSteps\Schemas;

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
                TextInput::make('chatbot_flow_id')
                    ->required()
                    ->numeric(),
                TextInput::make('step_order')
                    ->required()
                    ->numeric(),
                TextInput::make('step_key')
                    ->default(null),
                Textarea::make('question')
                    ->required()
                    ->columnSpanFull(),
                Select::make('input_type')
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
                    ->required(),
                TextInput::make('placeholder')
                    ->default(null),
                Toggle::make('is_required')
                    ->required(),
            ]);
    }
}
