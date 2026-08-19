<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KnowledgeSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('knowledgeCategory.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('pages')
                    ->label('Pages'),

                TextColumn::make('chunks')
                    ->label('Chunks'),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->since(),

                TextColumn::make('error')
                    ->label('Error')
                    ->limit(60)
                    ->tooltip(fn ($record) => $record->error)
                    ->color('danger')
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}