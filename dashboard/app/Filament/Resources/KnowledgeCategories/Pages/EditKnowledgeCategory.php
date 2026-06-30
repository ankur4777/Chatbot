<?php

namespace App\Filament\Resources\KnowledgeCategories\Pages;

use App\Filament\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeCategory extends EditRecord
{
    protected static string $resource = KnowledgeCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
