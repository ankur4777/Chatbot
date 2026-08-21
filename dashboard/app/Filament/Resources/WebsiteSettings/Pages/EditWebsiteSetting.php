<?php

namespace App\Filament\Resources\WebsiteSettings\Pages;

use App\Filament\Resources\WebsiteSettings\WebsiteSettingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWebsiteSetting extends EditRecord
{
    protected static string $resource = WebsiteSettingResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $position = $data['position'] ?? [];

        $data['position_horizontal'] =
            $position['horizontal'] ?? 'right';

        $data['position_horizontal_value'] =
            $position['horizontal_value'] ?? 25;

        $data['position_vertical'] =
            $position['vertical'] ?? 'bottom';

        $data['position_vertical_value'] =
            $position['vertical_value'] ?? 25;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}