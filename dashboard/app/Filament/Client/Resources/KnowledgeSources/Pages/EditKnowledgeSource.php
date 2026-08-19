<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Pages;

use App\Filament\Client\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Models\KnowledgeCategory;
use App\Models\Website;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditKnowledgeSource extends EditRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $user = auth()->user();

        if ($user && $user->role === 'owner') {

            // Verify selected website belongs to the Owner's company.
            $website = Website::where('id', $data['website_id'] ?? null)
                ->where('company_id', $user->company_id)
                ->firstOrFail();

            // Verify selected category belongs to that website.
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

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}