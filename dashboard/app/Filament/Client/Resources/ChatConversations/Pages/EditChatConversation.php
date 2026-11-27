<?php

namespace App\Filament\Client\Resources\ChatConversations\Pages;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use Filament\Resources\Pages\EditRecord;

class EditChatConversation extends EditRecord
{
    protected static string $resource = ChatConversationResource::class;
}