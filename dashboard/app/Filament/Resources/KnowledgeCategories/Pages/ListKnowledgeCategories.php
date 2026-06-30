<?php

namespace App\Filament\Resources\KnowledgeCategories\Pages;

use App\Filament\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKnowledgeCategories extends ListRecords
{
    protected static string $resource = KnowledgeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
