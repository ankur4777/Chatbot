<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeCategory extends EditRecord
{
    protected static string $resource = KnowledgeCategoryResource::class;
}