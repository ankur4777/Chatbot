<?php

namespace App\Filament\Client\Resources\ChatMessages\Pages;

use App\Filament\Client\Resources\ChatMessages\ChatMessageResource;
use Filament\Resources\Pages\EditRecord;

class EditChatMessage extends EditRecord
{
    protected static string $resource = ChatMessageResource::class;
}