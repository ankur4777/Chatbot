<?php

namespace App\Filament\Resources\GalleryImages\Pages;

use App\Filament\Resources\GalleryImages\GalleryImageResource;
use Filament\Resources\Pages\ListRecords;

class ListGalleryImages extends ListRecords
{
    protected static string $resource = GalleryImageResource::class;
}
