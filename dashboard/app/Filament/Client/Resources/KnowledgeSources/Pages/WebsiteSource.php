<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Pages;

use App\Filament\Client\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Models\KnowledgeSource;
use App\Models\Website;
use App\Support\BrowserTime;
use App\Services\KnowledgeSourceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteSources extends ListRecords
{
    protected static string $resource = KnowledgeSourceResource::class;

    public int $websiteId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        parent::mount();
    }

    protected function getWebsite(): Website
    {
        $user = auth()->user();

        return Website::query()
            ->whereKey($this->websiteId)
            ->where('company_id', $user->company_id)
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->getWebsite()->name . ' Sources';
    }

    protected function getTableQuery(): Builder
    {
        return KnowledgeSource::query()
            ->where('website_id', $this->websiteId)
            ->whereHas('website', function ($query) {
                $query->where(
                    'company_id',
                    auth()->user()->company_id
                );
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Knowledge Source'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('knowledgeCategory.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(
                        fn (string $state): string => match ($state) {
                            'completed' => 'success',
                            'processing' => 'warning',
                            'failed' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make('pages')
                    ->label('Pages')
                    ->sortable(),

                TextColumn::make('last_synced_at')
                    ->label('Last Synced')
                    ->since()
                    ->placeholder('Not synced')
                    ->tooltip(
                        fn ($record) =>
                            $record->last_synced_at
            ? BrowserTime::format(
                $record->last_synced_at,
                'd M Y, h:i A'
            )
                                : 'Not synced'
                    )
                    ->sortable(),

                TextColumn::make('error')
                    ->label('Error')
                    ->limit(20)
                    ->tooltip(fn ($record) => $record->error)
                    ->color('danger')
                    ->placeholder('—')
                    ->wrap(),
            ])

            ->recordActions([

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
                ->body(
                    $result['message']
                    ?? 'Unable to sync the knowledge source.'
                )
                ->danger()
                ->send();
        }),

    EditAction::make(),

    DeleteAction::make()
        ->requiresConfirmation(),
]);
    }
}