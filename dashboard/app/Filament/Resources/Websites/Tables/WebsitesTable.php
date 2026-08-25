<?php

namespace App\Filament\Resources\Websites\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WebsitesTable
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
        ->label('Website')
        ->searchable()
        ->sortable(),

    TextColumn::make('domain')
        ->searchable()
        ->copyable(),

    IconColumn::make('status')
        ->label('Active')
        ->boolean(),

    TextColumn::make('created_at')
    ->label('Created At')
    ->since()
    ->tooltip(fn ($record) => $record->created_at?->format('d M Y, h:i A'))
    ->sortable(),

    TextColumn::make('updated_at')
        ->label('Updated At')
        ->since()
        ->sortable()
            ->tooltip(fn ($record) => $record->updated_at?->format('d M Y, h:i A'))

        ->toggleable(isToggledHiddenByDefault: true),
])
            ->filters([

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
