<?php

namespace App\Filament\Resources\ChatbotQuickReplies\Pages;

use App\Filament\Resources\ChatbotQuickReplies\ChatbotQuickReplyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChatbotQuickReplies extends ListRecords
{
    protected static string $resource = ChatbotQuickReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
