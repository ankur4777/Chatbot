<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use App\Models\Website;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeCategory extends EditRecord
{
    protected static string $resource = KnowledgeCategoryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->role === 'owner') {
            $website = Website::where('id', $data['website_id'] ?? null)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            $data['website_id'] = $website->id;
        }

        return $data;
    }
}