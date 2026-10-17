<?php

namespace App\Filament\Client\Resources\GalleryImages\Pages;

use App\Filament\Client\Resources\GalleryImages\GalleryImageResource;
use App\Models\Website;
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
        $website = Website::query()
            ->whereKey($data['website_id'])
            ->where('company_id', auth()->user()?->company_id)
            ->firstOrFail();

        $data['company_id'] = $website->company_id;

        return $data;
    }
}
