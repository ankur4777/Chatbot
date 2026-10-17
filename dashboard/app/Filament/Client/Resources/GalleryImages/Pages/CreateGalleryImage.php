<?php

namespace App\Filament\Client\Resources\GalleryImages\Pages;

use App\Filament\Client\Resources\GalleryImages\GalleryImageResource;
use App\Models\Website;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryImage extends CreateRecord
{
    protected static string $resource = GalleryImageResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $website = Website::query()
            ->whereKey($data['website_id'])
            ->where('company_id', auth()->user()?->company_id)
            ->firstOrFail();

        $data['company_id'] = $website->company_id;

        return $data;
    }
}
