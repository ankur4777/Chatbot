<?php

namespace App\Filament\Resources\KnowledgeChunks\Pages;

use App\Filament\Resources\KnowledgeChunks\KnowledgeChunkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeChunk extends EditRecord
{
    protected static string $resource = KnowledgeChunkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
