<?php

namespace App\Filament\Client\Resources\ChatMessages\Pages;

use App\Filament\Client\Resources\ChatMessages\ChatMessageResource;
use Filament\Resources\Pages\ListRecords;

class ListChatMessages extends ListRecords
{
    protected static string $resource = ChatMessageResource::class;
}