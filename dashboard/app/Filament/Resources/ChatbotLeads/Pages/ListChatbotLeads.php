<?php

namespace App\Filament\Resources\ChatbotLeads\Pages;

use App\Filament\Resources\ChatbotLeads\ChatbotLeadResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChatbotLeads extends ListRecords
{
    protected static string $resource = ChatbotLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
