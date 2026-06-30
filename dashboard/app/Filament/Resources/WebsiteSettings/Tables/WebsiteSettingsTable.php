<?php

namespace App\Filament\Resources\WebsiteSettings\Tables;

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
                    ->searchable(),
                TextColumn::make('chatbot_name')
                    ->searchable(),
                TextColumn::make('welcome_message')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->welcome_message),
                TextColumn::make('placeholder')
                    ->limit(30),
                TextColumn::make('temperature')
                    ->sortable(),
                TextColumn::make('primary_color')
                    ->searchable(),
                TextColumn::make('position')
                    ->badge(),
                IconColumn::make('enable_chatbot')
                    ->boolean(),
                IconColumn::make('enable_live_chat')
                    ->boolean(),
                IconColumn::make('show_connect_agent')
                    ->boolean(),
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
