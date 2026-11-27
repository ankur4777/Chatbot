<?php

namespace App\Filament\Client\Resources\GalleryImages\Tables;

use App\Services\WebsiteFeatureService;
use App\Models\WebsiteGalleryImage;
use App\Support\BrowserTime;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
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

                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                TextColumn::make('category')
                    ->label('Category')
                    ->searchable()
                    ->sortable()
                    ->badge(),

                TextColumn::make('description')
                    ->searchable()
                    ->limit(60)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('tags')
                    ->label('Tags')
                    ->badge()
                    ->separator(',')
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($tagsQuery) use ($search): void {
                            $tagsQuery
                                ->whereJsonContains('tags', $search)
                                ->orWhere('tags', 'like', '%' . $search . '%');
                        });
                    }),

                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->disabled(fn (WebsiteGalleryImage $record): bool => ! auth()->user()?->can('update', $record))
                    ->label('Active')
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->tooltip(fn (WebsiteGalleryImage $record): string => BrowserTime::format($record->updated_at))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('website_id')
                    ->label('Website')
                    ->options(fn (): array => app(WebsiteFeatureService::class)->galleryWebsitesForUser(auth()->user())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ]);
    }
}
