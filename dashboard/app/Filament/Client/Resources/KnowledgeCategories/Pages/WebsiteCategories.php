<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use App\Models\KnowledgeCategory;
use App\Models\Website;
use App\Support\BrowserTime;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteCategories extends ListRecords
{
    protected static string $resource = KnowledgeCategoryResource::class;

    public int $websiteId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        // Verify that this website belongs to logged-in client.
        $this->getWebsite();

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
        return $this->getWebsite()->name . ' Categories';
    }

    protected function getTableQuery(): Builder
    {
        return KnowledgeCategory::query()
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
                ->label('New Knowledge Category'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([
                TextColumn::make('name')
                    ->label('Category')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Description')
                    ->limit(30)
                    ->tooltip(fn ($record) => $record->description)
                    ->placeholder('—')
                    ->wrap(),

                TextColumn::make('knowledge_sources_count')
                    ->label('Sources')
                    ->counts('knowledgeSources')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->since()
                    ->tooltip(
    fn ($record) =>
        $record->created_at
            ? BrowserTime::format(
                $record->created_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->since()
                    ->tooltip(
    fn ($record) =>
        $record->updated_at
            ? BrowserTime::format(
                $record->updated_at,
                'd M Y, h:i A'
            )
            : 'N/A'
)
                    ->sortable(),
            ])

            ->recordActions([
                EditAction::make(),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ]);
    }
}