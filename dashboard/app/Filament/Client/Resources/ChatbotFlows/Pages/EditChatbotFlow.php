<?php

namespace App\Filament\Client\Resources\ChatbotFlows\Pages;

use App\Filament\Client\Resources\ChatbotFlows\ChatbotFlowResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\DeleteAction;
class EditChatbotFlow extends EditRecord
{
    protected static string $resource = ChatbotFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation(),
        ];
    }
    public function getTitle(): string
{
    return 'Manage ' . $this->record->name;
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