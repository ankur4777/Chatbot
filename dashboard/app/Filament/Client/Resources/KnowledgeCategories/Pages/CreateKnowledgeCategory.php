<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeCategory extends CreateRecord
{
    protected static string $resource = KnowledgeCategoryResource::class;
}
