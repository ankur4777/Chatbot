<?php

namespace App\Filament\Resources\ChatbotLeads\Pages;

use App\Filament\Resources\ChatbotLeads\ChatbotLeadResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotLead extends EditRecord
{
    protected static string $resource = ChatbotLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
