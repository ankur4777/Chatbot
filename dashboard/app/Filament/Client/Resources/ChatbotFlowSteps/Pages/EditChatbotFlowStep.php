<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps\Pages;

use App\Filament\Client\Resources\ChatbotFlowSteps\ChatbotFlowStepResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotFlowStep extends EditRecord
{
    protected static string $resource = ChatbotFlowStepResource::class;

    public function getTitle(): string
    {
        return 'Edit Chatbot Step';
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Changes'),

            $this->getCancelFormAction(),
        ];
    }
}