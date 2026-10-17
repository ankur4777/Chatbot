<?php

namespace App\Filament\Client\Resources\GalleryImages\Pages;

use App\Filament\Client\Resources\GalleryImages\GalleryImageResource;
use App\Models\Website;
use App\Models\WebsiteGalleryImage;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGalleryImages extends ListRecords
{
    protected static string $resource = GalleryImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),

            Action::make('bulkUpload')
                ->label('Upload Multiple')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    Select::make('website_id')
                        ->label('Website')
                        ->options(fn (): array => Website::query()
                            ->where('company_id', auth()->user()?->company_id)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('title')
                        ->label('Title prefix')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('category')
                        ->label('Category / Subject')
                        ->placeholder('ring, necklace, marble, kitchen')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('description')
                        ->rows(3)
                        ->maxLength(5000),

                    TagsInput::make('tags')
                        ->label('Tags / Keywords'),

                    FileUpload::make('images')
                        ->label('Images')
                        ->disk(config('gallery.storage_disk', 'public'))
                        ->directory(config('gallery.storage_directory', 'gallery-images'))
                        ->visibility('public')
                        ->multiple()
                        ->image()
                        ->acceptedFileTypes([
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ])
                        ->maxSize(config('gallery.max_upload_kb', 5120))
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $website = Website::query()
                        ->whereKey($data['website_id'])
                        ->where('company_id', auth()->user()?->company_id)
                        ->firstOrFail();

                    foreach ((array) ($data['images'] ?? []) as $index => $path) {
                        WebsiteGalleryImage::create([
                            'company_id' => $website->company_id,
                            'website_id' => $website->id,
                            'category' => $data['category'],
                            'title' => count($data['images']) > 1
                                ? $data['title'] . ' ' . ($index + 1)
                                : $data['title'],
                            'description' => $data['description'] ?? null,
                            'tags' => $data['tags'] ?? [],
                            'image_path' => $path,
                            'is_active' => true,
                        ]);
                    }

                    Notification::make()
                        ->title('Images uploaded successfully.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
