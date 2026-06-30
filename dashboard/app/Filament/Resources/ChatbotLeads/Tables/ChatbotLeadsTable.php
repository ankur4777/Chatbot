<?php

namespace App\Filament\Resources\ChatbotLeads\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
    ->label('Website')
    ->searchable()
    ->sortable(),
                TextColumn::make('visitor.name')
    ->label('Visitor')
    ->searchable()
    ->sortable(),
               TextColumn::make('conversation.id')
    ->label('Conversation')
    ->sortable(),
                TextColumn::make('name')
    ->label('Full Name')
    ->searchable()
    ->sortable(),
                TextColumn::make('email')
    ->label('Email')
    ->searchable()
    ->copyable(),
                TextColumn::make('phone')
    ->copyable(),
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
