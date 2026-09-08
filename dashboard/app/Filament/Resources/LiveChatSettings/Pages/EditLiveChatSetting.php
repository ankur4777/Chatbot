<?php

namespace App\Filament\Resources\LiveChatSettings\Pages;

use App\Filament\Resources\LiveChatSettings\LiveChatSettingResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;

class EditLiveChatSetting extends EditRecord
{
    protected static string $resource = LiveChatSettingResource::class;

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->label('Save Changes');
    }
}
