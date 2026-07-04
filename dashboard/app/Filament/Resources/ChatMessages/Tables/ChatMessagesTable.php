<?php

namespace App\Filament\Resources\ChatMessages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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
    ->sortable(),

TextColumn::make('sender_type')
    ->label('Sender')
    ->badge()
    ->sortable(),

TextColumn::make('message')
    ->label('Message')
    ->wrap(),

TextColumn::make('created_at')
    ->label('Time')
    ->since()
    ->sortable(),
                    // ->limit(80)
                    // ->tooltip(fn ($record) => $record->message),
                
            ])
            ->filters([
                //
            ])
            ->recordActions([
    //
])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
