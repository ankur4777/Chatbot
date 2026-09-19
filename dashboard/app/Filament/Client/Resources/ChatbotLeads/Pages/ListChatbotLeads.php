<?php

namespace App\Filament\Client\Resources\ChatbotLeads\Pages;

use App\Filament\Client\Resources\ChatbotLeads\ChatbotLeadResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListChatbotLeads extends ListRecords
{
    protected static string $resource = ChatbotLeadResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadLeads')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('client.chatbot-leads.download')),
        ];
    }
}
