<?php

namespace App\Filament\Client\Resources\GalleryImages;

use App\Filament\Client\Resources\GalleryImages\Pages\CreateGalleryImage;
use App\Filament\Client\Resources\GalleryImages\Pages\EditGalleryImage;
use App\Filament\Client\Resources\GalleryImages\Pages\ListGalleryImages;
use App\Filament\Client\Resources\GalleryImages\Schemas\GalleryImageForm;
use App\Filament\Client\Resources\GalleryImages\Tables\GalleryImagesTable;
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

    protected static ?int $navigationSort = 95;

    protected static ?string $modelLabel = 'Image Gallery';

    protected static ?string $pluralModelLabel = 'Image Gallery';

    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()->with('website')
            ->where('company_id', $user?->company_id)
            ->whereIn('website_id', app(\App\Services\WebsiteFeatureService::class)
                ->galleryWebsitesForUser($user)->select('id'));
    }

    public static function form(Schema $schema): Schema
    {
        return GalleryImageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GalleryImagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGalleryImages::route('/'),
            'create' => CreateGalleryImage::route('/create'),
            'edit' => EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
