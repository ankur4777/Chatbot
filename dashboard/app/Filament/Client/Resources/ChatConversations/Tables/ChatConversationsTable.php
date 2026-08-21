<?php

namespace App\Filament\Client\Resources\ChatConversations\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
                    ->searchable()
                    ->sortable(),

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
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}