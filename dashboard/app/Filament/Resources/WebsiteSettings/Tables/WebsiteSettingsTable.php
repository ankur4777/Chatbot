<?php

namespace App\Filament\Resources\WebsiteSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use App\Support\BrowserTime;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\DeleteAction;
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
                ->label('Chatbot Name')
                    ->searchable(),
                IconColumn::make('enable_chatbot')
                 ->label('Chatbot')
                    ->boolean(),
                IconColumn::make('enable_live_chat')
                 ->label('Live Chat')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->since()
                    ->label('Created At')
                      ->tooltip(
    fn ($record) =>
        $record->created_at
            ? BrowserTime::format(
                $record->created_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)

                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                ->label('Updated At')
                    ->since()
                    ->sortable()
                   ->tooltip(
    fn ($record) =>
        $record->updated_at
            ? BrowserTime::format(
                $record->updated_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)

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
