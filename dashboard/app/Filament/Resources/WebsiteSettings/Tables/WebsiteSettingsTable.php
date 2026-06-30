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
                    ->searchable(),
                TextColumn::make('placeholder')
                    ->searchable(),
                TextColumn::make('language')
                    ->searchable(),
                TextColumn::make('model')
                    ->searchable(),
                TextColumn::make('temperature')
                    ->numeric()
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
