<?php

namespace App\Filament\Client\Resources\LiveChatSettings\Pages;

use App\Filament\Client\Resources\LiveChatSettings\LiveChatSettingResource;
use Filament\Resources\Pages\ListRecords;

class ListLiveChatSettings extends ListRecords
{
    protected static string $resource = LiveChatSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
