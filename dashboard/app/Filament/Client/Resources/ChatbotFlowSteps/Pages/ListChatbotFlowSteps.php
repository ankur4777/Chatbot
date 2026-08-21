<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps\Pages;

use App\Filament\Client\Resources\ChatbotFlowSteps\ChatbotFlowStepResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChatbotFlowSteps extends ListRecords
{
    protected static string $resource = ChatbotFlowStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}