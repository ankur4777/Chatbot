<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Pages;

use App\Filament\Client\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Services\KnowledgeSourceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewKnowledgeSource extends ViewRecord
{
    protected static string $resource = KnowledgeSourceResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Action::make('sync')
                ->label(
                    fn () => $this->record->status === 'completed'
                        ? 'Re-sync'
                        : 'Sync'
                )
                ->icon('heroicon-o-arrow-path')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading(
                    fn () => $this->record->status === 'completed'
                        ? 'Re-sync Knowledge Source'
                        : 'Sync Knowledge Source'
                )
                ->modalDescription(
                    'This will process the knowledge source and update its knowledge base and embeddings.'
                )
                ->action(function () {

                    set_time_limit(300);

                    $result = app(KnowledgeSourceService::class)
                        ->import($this->record);

                    if ($result['success'] ?? false) {

                        $this->record->refresh();

                        Notification::make()
                            ->title('Knowledge synced successfully.')
                            ->success()
                            ->send();

                        return;
                    }

                    $this->record->refresh();

                    Notification::make()
                        ->title('Knowledge sync failed.')
                        ->body(
                            $result['message']
                            ?? 'Unable to sync the knowledge source.'
                        )
                        ->danger()
                        ->send();
                }),

            EditAction::make(),
        ];
    }
}