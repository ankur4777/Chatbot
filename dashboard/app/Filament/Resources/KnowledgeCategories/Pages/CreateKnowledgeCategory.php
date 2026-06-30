<?php

namespace App\Filament\Resources\KnowledgeCategories\Pages;

use App\Filament\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeCategory extends CreateRecord
{
    protected static string $resource = KnowledgeCategoryResource::class;
}
