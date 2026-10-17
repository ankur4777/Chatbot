<?php

namespace App\Filament\Client\Resources\GalleryImages\Schemas;

use App\Models\Website;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GalleryImageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
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
                ->required()
                ->maxLength(255),

            TextInput::make('category')
                ->label('Category / Subject')
                ->placeholder('ring, necklace, marble, kitchen')
                ->helperText('Use the main subject of this image. Categories are free-text and are not limited to a fixed list.')
                ->required()
                ->maxLength(255),

            Textarea::make('description')
                ->rows(4)
                ->maxLength(5000)
                ->columnSpanFull(),

            TagsInput::make('tags')
                ->label('Tags / Keywords')
                ->placeholder('modern, modular kitchen, marble')
                ->columnSpanFull(),

            FileUpload::make('image_path')
                ->label('Image')
                ->disk(config('gallery.storage_disk', 'public'))
                ->directory(config('gallery.storage_directory', 'gallery-images'))
                ->visibility('public')
                ->image()
                ->acceptedFileTypes([
                    'image/jpeg',
                    'image/png',
                    'image/webp',
                ])
                ->maxSize(config('gallery.max_upload_kb', 5120))
                ->required()
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ]);
    }
}
