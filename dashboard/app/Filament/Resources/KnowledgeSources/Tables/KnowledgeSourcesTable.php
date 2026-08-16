<?php

namespace App\Filament\Resources\KnowledgeSources\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use App\Services\KnowledgeSourceService;
use Filament\Notifications\Notification;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\BadgeColumn;
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
                    ->searchable(),

                TextColumn::make('title')
                    ->searchable(),
                
                TextColumn::make('knowledgeCategory.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                BadgeColumn::make('type'),

                BadgeColumn::make('status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'processing',
                        'success' => 'completed',
                        'danger' => 'failed',
                    ]),

                TextColumn::make('error')
    ->label('Error')
    ->limit(60)
    ->tooltip(fn ($record) => $record->error)
    ->color('danger')
    ->wrap(),
                BadgeColumn::make('pages')
                    ->color('info'),

                BadgeColumn::make('chunks')
                    ->color('success'),

                TextColumn::make('last_synced_at')
                    ->since(),

            ])
            ->filters([

            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('sync')
            ->label('Sync')
            ->icon('heroicon-o-arrow-path')
            ->color('success')
            ->requiresConfirmation()
            ->action(function ($record) {

        app(KnowledgeSourceService::class)
            ->import($record);

        Notification::make()
            ->title('Knowledge synced successfully.')
            ->success()
            ->send();

    }),
    
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}