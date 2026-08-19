<?php

namespace App\Filament\Client\Resources\WebsiteSettings\Pages;

use App\Filament\Client\Resources\WebsiteSettings\WebsiteSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsiteSetting extends CreateRecord
{
    protected static string $resource = WebsiteSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        // Owner can only create settings for their own company's website.
        if ($user && $user->role === 'owner') {
            $website = \App\Models\Website::where('id', $data['website_id'] ?? null)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            $data['website_id'] = $website->id;
        }

        return $data;
    }
}