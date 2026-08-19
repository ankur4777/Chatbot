<?php

namespace App\Filament\Client\Resources\WebsiteSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsiteSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chatbot_name')
                    ->label('Chatbot Name')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('enable_chatbot')
                    ->label('Chatbot')
                    ->boolean(),

                IconColumn::make('enable_live_chat')
                    ->label('Live Chat')
                    ->boolean(),

                TextColumn::make('position')
                    ->badge(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
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