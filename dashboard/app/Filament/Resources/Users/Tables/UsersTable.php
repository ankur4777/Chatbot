<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
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
                TextColumn::make('phone')
    ->label('Phone')
    ->searchable()
    ->copyable()
    ->placeholder('N/A'),
                TextColumn::make('role')
    ->formatStateUsing(fn (string $state): string => match ($state) {
        'super_admin' => 'Super Admin',
        'owner' => 'Client',
        'agent' => 'Agent',
        default => ucfirst(str_replace('_', ' ', $state)),
    })
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
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (User $record): bool => $record->role !== 'super_admin',
            );
    }
}
