<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use App\Support\BrowserTime;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KnowledgeCategoriesTable
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
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->description)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('knowledge_sources_count')
                    ->label('Sources')
                    ->counts('knowledgeSources')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                           BrowserTime::format($record->created_at)
                    )
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            BrowserTime::format($record->updated_at)
                    )
                    ->sortable(),
            ])

            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ]);
    }
}