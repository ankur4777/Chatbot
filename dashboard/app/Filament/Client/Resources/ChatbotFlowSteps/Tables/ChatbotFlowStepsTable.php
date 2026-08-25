<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotFlowStepsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('flow.name')
                    ->label('Flow')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('step_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('step_key')
                    ->label('Step Key')
                    ->searchable(),

                TextColumn::make('question')
                    ->label('Question')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),

                TextColumn::make('input_type')
                    ->label('Input Type')
                    ->badge(),

                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}