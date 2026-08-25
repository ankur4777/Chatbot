<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Pages;

use App\Filament\Client\Resources\KnowledgeSources\KnowledgeSourceResource;
use App\Models\KnowledgeSource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListKnowledgeSources extends ListRecords
{
    protected static string $resource = KnowledgeSourceResource::class;

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
            ->modifyQueryUsing(function ($query) {

                // Main page par har website ki sirf ek summary row.
                $query->whereIn('id', function ($subQuery) {
                    $subQuery
                        ->from('knowledge_sources')
                        ->selectRaw('MAX(id)')
                        ->groupBy('website_id');
                });
            })

            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_sources')
                    ->label('Total Sources')
                    ->state(
                        fn ($record) =>
                            KnowledgeSource::query()
                                ->where('website_id', $record->website_id)
                                ->count()
                    ),
            ])

            ->recordUrl(
                fn ($record) =>
                    KnowledgeSourceResource::getUrl(
                        'website-sources',
                        [
                            'website' => $record->website_id,
                        ]
                    )
            )

            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            KnowledgeSourceResource::getUrl(
                                'website-sources',
                                [
                                    'website' => $record->website_id,
                                ]
                            )
                    ),
            ]);
    }
}