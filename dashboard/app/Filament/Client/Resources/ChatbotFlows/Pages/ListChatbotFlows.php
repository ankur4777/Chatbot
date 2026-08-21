<?php

namespace App\Filament\Client\Resources\ChatbotFlows\Pages;

use App\Filament\Client\Resources\ChatbotFlows\ChatbotFlowResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

class ListChatbotFlows extends ListRecords
{
    protected static string $resource = ChatbotFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}