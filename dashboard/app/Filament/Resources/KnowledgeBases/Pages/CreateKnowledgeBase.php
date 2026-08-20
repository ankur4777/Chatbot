<?php

namespace App\Filament\Resources\KnowledgeBases\Pages;

use App\Filament\Resources\KnowledgeBases\KnowledgeBaseResource;
use App\Services\KnowledgeChunkService;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeBase extends CreateRecord
{
    protected static string $resource = KnowledgeBaseResource::class;

    protected function afterCreate(): void
    {
        app(KnowledgeChunkService::class)
            ->generate($this->record);
    }
}