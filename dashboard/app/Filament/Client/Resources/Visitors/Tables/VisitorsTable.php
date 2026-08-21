<?php

namespace App\Filament\Client\Resources\Visitors\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Visitor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Anonymous'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->copyable()
                    ->placeholder('-'),

                TextColumn::make('session_id')
                    ->label('Session ID')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->session_id),

                TextColumn::make('ip_address')
                    ->label('IP Address'),

                TextColumn::make('created_at')
                    ->label('First Seen')
                    ->since()
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                //
            ])
            ->toolbarActions([
                //
            ]);
    }
}