<?php

namespace App\Filament\Resources\ChatbotFlows\Pages;

use App\Filament\Resources\ChatbotFlows\ChatbotFlowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

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
