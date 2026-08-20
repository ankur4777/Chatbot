<?php

namespace App\Filament\Resources\KnowledgeSources\Pages;

use App\Filament\Resources\KnowledgeSources\KnowledgeSourceResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use App\Services\AIService;
use App\Services\EmbeddingService;
use App\Models\KnowledgeBase;
use App\Services\KnowledgeSourceService;
use Filament\Notifications\Notification;

class EditKnowledgeSource extends EditRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function getHeaderActions(): array
{
    return [

        Action::make('sync')
            ->label('Sync Knowledge')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->action(function () {

                app(KnowledgeSourceService::class)
                    ->import($this->record);

                Notification::make()
                    ->title('Knowledge synced successfully.')
                    ->success()
                    ->send();

            }),

        ViewAction::make(),

        DeleteAction::make()
    ->requiresConfirmation()

    ->before(function () {

        $websiteId = $this->record->website_id;

        $knowledgeBase = $this->record->knowledgeBase;

        if ($knowledgeBase) {

            $knowledgeBase->chunks()->delete();

            $knowledgeBase->delete();

        }

    })

    ->after(function () {

        $knowledgeBase = KnowledgeBase::where(
    'website_id',
    $this->record->website_id
)->latest()->first();

if ($knowledgeBase) {

    app(EmbeddingService::class)->sync($knowledgeBase);

} else {

    app(AIService::class)->clearKnowledge(
        $this->record->website_id
    );

}
    }),

    ];
}
}
