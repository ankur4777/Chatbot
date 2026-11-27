<?php

namespace App\Filament\Client\Resources\GalleryImages\Pages;

use App\Filament\Client\Resources\GalleryImages\GalleryImageResource;
use App\Services\WebsiteFeatureService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGalleryImage extends EditRecord
{
    protected static string $resource = GalleryImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        abort_unless(auth()->user()?->can('update', $this->getRecord()), 403);
        $website = app(WebsiteFeatureService::class)->galleryWebsitesForUser(auth()->user())
                        ->whereKey($data['website_id'])
            ->firstOrFail();

        $data['company_id'] = $website->company_id;

        return $data;
    }
}
