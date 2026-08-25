<?php

namespace App\Filament\Client\Resources\ChatConversations\Pages;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use Filament\Resources\Pages\ListRecords;

class ListChatConversations extends ListRecords
{
    protected static string $resource = ChatConversationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}