<?php

namespace App\Filament\Resources\ChatbotFlowSteps\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
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
                TextColumn::make('chatbot_flow_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('step_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('step_key')
                    ->searchable(),
                TextColumn::make('input_type')
                    ->badge(),
                TextColumn::make('placeholder')
                    ->searchable(),
                IconColumn::make('is_required')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
