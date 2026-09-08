<?php

namespace App\Filament\Client\Resources\LiveChatSettings\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LiveChatSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('enable_live_chat')
                    ->label('Live Chat Enabled')
                    ->boolean(),

                TextColumn::make('offline_behavior')
                    ->label('Offline Behavior')
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            $state === 'hide_button'
                                ? 'Hide button'
                                : 'Show offline form'
                    ),

                TextColumn::make('max_active_chats_per_agent')
                    ->label('Max Chats / Agent'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
