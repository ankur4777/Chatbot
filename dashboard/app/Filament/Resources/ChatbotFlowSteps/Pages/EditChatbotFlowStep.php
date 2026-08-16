<?php

namespace App\Filament\Resources\ChatbotFlowSteps\Pages;

use App\Filament\Resources\ChatbotFlowSteps\ChatbotFlowStepResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotFlowStep extends EditRecord
{
    protected static string $resource = ChatbotFlowStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
