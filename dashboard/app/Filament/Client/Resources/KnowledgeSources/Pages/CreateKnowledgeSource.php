<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Pages;

use App\Filament\Client\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Models\KnowledgeCategory;
use App\Models\Website;
use Filament\Resources\Pages\CreateRecord;

class CreateKnowledgeSource extends CreateRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->role === 'owner') {

            // Verify that the selected website belongs to the Owner's company.
            $website = Website::where('id', $data['website_id'] ?? null)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            // Verify that the selected category belongs to
            // the same company.
            $category = KnowledgeCategory::where(
                    'id',
                    $data['knowledge_category_id'] ?? null
                )
                ->where('website_id', $website->id)
                ->firstOrFail();

            $data['website_id'] = $website->id;
            $data['knowledge_category_id'] = $category->id;
        }

        return $data;
    }
}