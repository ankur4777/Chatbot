<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\URL;

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
            ])
            ->filters([
                
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('viewClientDashboard')
                    ->label('View Client Dashboard')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->visible(
                        fn (User $record): bool =>
                            $record->role === 'owner' &&
                            $record->status &&
                            (bool) $record->company?->status
                    )
                    ->url(
                        fn (User $record): string => URL::temporarySignedRoute(
                            'admin.users.view-client-dashboard',
                            now()->addMinutes(5),
                            ['user' => $record],
                            false
                        )
                    ),
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
