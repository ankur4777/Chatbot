<?php

namespace App\Filament\Resources\ChatbotFlows\Pages;

use App\Filament\Resources\ChatbotFlows\ChatbotFlowResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotFlow extends EditRecord
{
    protected static string $resource = ChatbotFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
