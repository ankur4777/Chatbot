<?php

namespace App\Filament\Client\Resources\ChatMessages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('conversation.id')
                    ->label('Conversation')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('sender_type')
                    ->label('Sender')
                    ->badge()
                    ->sortable(),

                TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->limit(100)
                    ->tooltip(fn ($record) => $record->message),

                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}