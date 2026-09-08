<?php

namespace App\Filament\Client\Resources\LiveChatSettings\Pages;

use App\Filament\Client\Resources\LiveChatSettings\LiveChatSettingResource;
use Filament\Resources\Pages\EditRecord;

class EditLiveChatSetting extends EditRecord
{
    protected static string $resource = LiveChatSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
