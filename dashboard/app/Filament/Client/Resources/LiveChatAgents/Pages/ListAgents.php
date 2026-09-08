<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Pages;

use App\Filament\Client\Resources\LiveChatAgents\AgentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgents extends ListRecords
{
    protected static string $resource = AgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
