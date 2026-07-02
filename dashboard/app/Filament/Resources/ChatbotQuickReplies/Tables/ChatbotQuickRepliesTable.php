<?php

namespace App\Filament\Resources\ChatbotQuickReplies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

class ChatbotQuickRepliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

    TextColumn::make('website.name')
        ->label('Website')
        ->searchable(),

    TextColumn::make('label')
        ->searchable(),

    TextColumn::make('value')
        ->searchable(),

    TextColumn::make('icon')
        ->placeholder('-'),

    TextColumn::make('sort_order')
        ->sortable(),

    IconColumn::make('is_active')
        ->boolean(),

    TextColumn::make('created_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),

    TextColumn::make('updated_at')
        ->dateTime()
        ->sortable()
        ->toggleable(isToggledHiddenByDefault: true),

]);
    }
}
