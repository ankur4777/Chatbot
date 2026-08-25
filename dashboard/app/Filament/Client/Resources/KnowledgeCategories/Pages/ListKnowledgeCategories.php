<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Pages;

use App\Filament\Client\Resources\KnowledgeCategories\KnowledgeCategoryResource;
use App\Models\KnowledgeCategory;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ListKnowledgeCategories extends ListRecords
{
    protected static string $resource = KnowledgeCategoryResource::class;

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
            ->modifyQueryUsing(function ($query) {

                // Main page par each website ki one summary row.
                $query->whereIn('id', function ($subQuery) {
                    $subQuery
                        ->from('knowledge_categories')
                        ->selectRaw('MAX(id)')
                        ->groupBy('website_id');
                });
            })

            ->columns([
                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_categories')
                    ->label('Total Categories')
                    ->state(
                        fn ($record) =>
                            KnowledgeCategory::query()
                                ->where('website_id', $record->website_id)
                                ->count()
                    ),
            ])

            ->recordUrl(
                fn ($record) =>
                    KnowledgeCategoryResource::getUrl(
                        'website-categories',
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
                            KnowledgeCategoryResource::getUrl(
                                'website-categories',
                                [
                                    'website' => $record->website_id,
                                ]
                            )
                    ),
            ]);
    }
}