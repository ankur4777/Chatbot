<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Tables;

use App\Services\KnowledgeSourceService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;


class KnowledgeSourcesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('knowledgeCategory.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('type')
                    ->badge(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'processing' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('pages')
                    ->label('Pages'),

                TextColumn::make('chunks')
                    ->label('Chunks'),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->since(),

                TextColumn::make('error')
                    ->label('Error')
                    ->limit(60)
                    ->tooltip(fn($record) => $record->error)
                    ->color('danger')
                    ->wrap(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->action(function ($record) {

                        set_time_limit(300);

                        $result = app(KnowledgeSourceService::class)
                            ->import($record);

                        if ($result['success'] ?? false) {

                            Notification::make()
                                ->title('Knowledge synced successfully.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Knowledge sync failed.')
                            ->body($result['message'] ?? 'Unable to sync knowledge.')
                            ->danger()
                            ->send();
                    }),

                ViewAction::make(),
                EditAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),

                Action::make('sync')
                    ->label('Sync')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Sync Knowledge Source')
                    ->modalDescription(
                        'This will process the knowledge source and update its knowledge base and embeddings.'
                    )
                    ->action(function ($record) {

                        $result = app(KnowledgeSourceService::class)
                            ->import($record);

                        if ($result['success'] ?? false) {
                            Notification::make()
                                ->title('Knowledge synced successfully.')
                                ->success()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title('Knowledge sync failed.')
                            ->body($result['message'] ?? 'Unable to sync the knowledge source.')
                            ->danger()
                            ->send();
                    }),

                EditAction::make(),
            ]);
    }
}
