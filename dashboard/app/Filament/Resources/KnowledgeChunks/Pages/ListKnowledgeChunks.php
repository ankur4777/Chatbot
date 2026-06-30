<?php

namespace App\Filament\Resources\KnowledgeChunks\Pages;

use App\Filament\Resources\KnowledgeChunks\KnowledgeChunkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeChunks extends ListRecords
{
    protected static string $resource = KnowledgeChunkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
