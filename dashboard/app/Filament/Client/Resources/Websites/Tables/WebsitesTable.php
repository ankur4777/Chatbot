<?php

namespace App\Filament\Client\Resources\Websites\Tables;
use Filament\Actions\EditAction;
use App\Support\BrowserTime;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Table;

class WebsitesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Domain')
                    ->searchable()
                    ->copyable(),

                IconColumn::make('status')
    ->label('Active')
    ->boolean(),

                TextColumn::make('created_at')
    ->label('Created At')
    ->since()
    ->tooltip(fn ($record) => BrowserTime::format($record->created_at)
)
    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->since()
                    ->sortable()
                    ->tooltip(fn ($record) =>  BrowserTime::format($record->updated_at))
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
            ]);
    }
}