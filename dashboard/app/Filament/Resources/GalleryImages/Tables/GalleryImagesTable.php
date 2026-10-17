<?php

namespace App\Filament\Resources\GalleryImages\Tables;

use App\Support\BrowserTime;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class GalleryImagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image_path')
                    ->label('Preview')
                    ->disk(config('gallery.storage_disk', 'public'))
                    ->height(58)
                    ->width(82),

                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onColor('success')
                    ->offColor('danger'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->tooltip(fn ($record): string =>
                        $record->updated_at
                            ? BrowserTime::format($record->updated_at)
                            : 'N/A'
                    )
                    ->sortable(),
            ])
            ->filters([])
            ->defaultSort('updated_at', 'desc');
    }
}
