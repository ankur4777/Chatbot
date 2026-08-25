<?php

namespace App\Filament\Client\Resources\WebsiteSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Support\BrowserTime;
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
                    ->searchable()
                    ->label('Chatbot Name'),


                IconColumn::make('enable_chatbot')
                    ->boolean()
                    ->label('Chatbot'),

                IconColumn::make('enable_live_chat')
                    ->boolean()
                    ->label('Live Chat'),

                TextColumn::make('created_at')
                    ->since()
                    ->label('Created At')
                    ->sortable()
                        ->tooltip(fn ($record) =>  BrowserTime::format($record->created_at))

                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->since()
                    ->label('Updated At')
                    ->sortable()
                        ->tooltip(fn ($record) =>  BrowserTime::format($record->updated_at))
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}