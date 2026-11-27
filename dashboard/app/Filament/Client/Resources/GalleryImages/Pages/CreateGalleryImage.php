<?php

namespace App\Filament\Client\Resources\GalleryImages\Pages;

use App\Filament\Client\Resources\GalleryImages\GalleryImageResource;
use App\Services\WebsiteFeatureService;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryImage extends CreateRecord
{
    protected static string $resource = GalleryImageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $website = app(WebsiteFeatureService::class)->galleryWebsitesForUser(auth()->user())
                        ->whereKey($data['website_id'])
            ->firstOrFail();

        $data['company_id'] = $website->company_id;

        return $data;
    }
}
