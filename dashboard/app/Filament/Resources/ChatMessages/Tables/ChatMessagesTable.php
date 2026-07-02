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
                    ->limit(80)
                    ->tooltip(fn ($record) => $record->message),
                TextColumn::make('attachment')
                    ->label('Attachment')
                    ->placeholder('-'),
                TextColumn::make('attachment_type')
                    ->label('Type')
                    ->badge(),
                TextColumn::make('created_at')
                    ->label('Time')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
            ])
            ->filters([
                //
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
