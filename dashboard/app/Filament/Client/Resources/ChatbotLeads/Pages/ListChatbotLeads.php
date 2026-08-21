<?php

namespace App\Filament\Client\Resources\ChatbotLeads\Pages;

use App\Filament\Client\Resources\ChatbotLeads\ChatbotLeadResource;
use Filament\Resources\Pages\ListRecords;

class ListChatbotLeads extends ListRecords
{
    protected static string $resource = ChatbotLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}