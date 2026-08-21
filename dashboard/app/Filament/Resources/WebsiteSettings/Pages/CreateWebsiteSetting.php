<?php

namespace App\Filament\Resources\WebsiteSettings\Pages;

use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsiteSetting extends CreateRecord
{
    protected static string $resource = WebsiteSettingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    $user = auth()->user();

    if ($user->role === 'owner') {
        $data['website_id'] = \App\Models\Website::where(
            'company_id',
            $user->company_id
        )->value('id');
    }

    $data['position'] = [
        'horizontal' => $data['position_horizontal'] ?? 'right',
        'horizontal_value' => (int) ($data['position_horizontal_value'] ?? 25),
        'vertical' => $data['position_vertical'] ?? 'bottom',
        'vertical_value' => (int) ($data['position_vertical_value'] ?? 25),
    ];

    unset(
        $data['position_horizontal'],
        $data['position_horizontal_value'],
        $data['position_vertical'],
        $data['position_vertical_value']
    );

    return $data;
}
}