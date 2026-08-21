<?php

namespace App\Filament\Client\Resources\Websites\Pages;

use App\Filament\Client\Resources\Websites\WebsiteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsite extends CreateRecord
{
    protected static string $resource = WebsiteResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        return $data;
    }
}