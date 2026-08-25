<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
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

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Changes'),

            $this->getCancelFormAction(),
        ];
    }
}