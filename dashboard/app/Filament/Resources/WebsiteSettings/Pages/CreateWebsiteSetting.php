<?php

namespace App\Filament\Resources\WebsiteSettings\Pages;
use Filament\Actions\Action;
use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWebsiteSetting extends CreateRecord
{
    protected static string $resource = WebsiteSettingResource::class;
    protected function getCreateAnotherFormAction(): Action
{
    return parent::getCreateAnotherFormAction()
        ->hidden();
}

    protected function mutateFormDataBeforeCreate(array $data): array
    {
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