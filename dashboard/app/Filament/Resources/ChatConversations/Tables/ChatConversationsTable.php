<?php

namespace App\Filament\Resources\ChatConversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('visitor.name')
                    ->label('Visitor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('assignedAgent.name')
                    ->label('Assigned Agent')
                    ->placeholder('Unassigned')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->since()
                    ->sortable(),
                TextColumn::make('ended_at')
                    ->label('Ended')
                    ->since()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                
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
