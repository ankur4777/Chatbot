<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
    ->label('Company')
    ->searchable()
    ->sortable(),
                TextColumn::make('name')
    ->label('Full Name')
    ->searchable()
    ->sortable(),
                TextColumn::make('email')
    ->label('Email')
    ->searchable()
    ->copyable(),
                TextColumn::make('role')
    ->badge()
    ->sortable(),
                TextColumn::make('created_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('status')
                    ->boolean(),
                IconColumn::make('is_online')
                    ->boolean(),
                TextColumn::make('last_seen_at')
    ->since()
    ->sortable(),
            ])
            ->filters([
                
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
