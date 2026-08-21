<?php

namespace App\Filament\Client\Resources\ChatbotFlows\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StepsRelationManager extends RelationManager
{
    protected static string $relationship = 'steps';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('step_order')
                    ->label('Step Order')
                    ->numeric()
                    ->required(),

                TextInput::make('step_key')
                    ->label('Step Key')
                    ->required()
                    ->maxLength(255)
                    ->helperText(
                        'Example: trip_type, budget, destination'
                    ),

                Textarea::make('question')
                    ->label('Question')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Select::make('input_type')
                    ->label('Input Type')
                    ->options([
                        'text' => 'Text',
                        'textarea' => 'Textarea',
                        'number' => 'Number',
                        'date' => 'Date',
                        'select' => 'Select',
                        'buttons' => 'Buttons',
                    ])
                    ->searchable()
                    ->required(),

                TextInput::make('placeholder')
                    ->label('Placeholder')
                    ->maxLength(255),

                Toggle::make('is_required')
                    ->label('Required')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('question')
            ->columns([
                TextColumn::make('step_order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('step_key')
                    ->label('Step Key')
                    ->searchable(),

                TextColumn::make('question')
                    ->label('Question')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('input_type')
                    ->label('Input Type')
                    ->badge(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}