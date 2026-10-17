<?php

namespace App\Filament\Resources\GalleryImages;

use App\Filament\Resources\GalleryImages\Pages\ListGalleryImages;
use App\Filament\Resources\GalleryImages\Tables\GalleryImagesTable;
use App\Models\WebsiteGalleryImage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class GalleryImageResource extends Resource
{
    protected static ?string $model = WebsiteGalleryImage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Chatbot';

    protected static ?int $navigationSort = 96;

    protected static ?string $modelLabel = 'Image Gallery';

    protected static ?string $pluralModelLabel = 'Image Gallery';

    protected static bool $shouldRegisterNavigation = false;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['website', 'company']);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return GalleryImagesTable::configure($table);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryImages::route('/'),
        ];
    }
}
