<?php

namespace App\Filament\Resources\ChatbotQuickReplies\Pages;

use App\Filament\Resources\ChatbotQuickReplies\ChatbotQuickReplyResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditChatbotQuickReply extends EditRecord
{
    protected static string $resource = ChatbotQuickReplyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
