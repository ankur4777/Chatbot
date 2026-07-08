<?php

namespace App\Filament\Resources\KnowledgeSources\Pages;

use App\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Services\KnowledgeSourceService;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeSource extends CreateRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function afterCreate(): void
    {
        app(KnowledgeSourceService::class)
            ->import($this->record);
    }
}