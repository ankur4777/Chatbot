<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Pages;

use App\Filament\Client\Resources\LiveChatAgents\AgentResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAgents extends ListRecords
{
    protected static string $resource = AgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadAgents')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('client.agents.download')),
            CreateAction::make(),
        ];
    }
}
