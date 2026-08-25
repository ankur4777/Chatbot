<?php

namespace App\Filament\Client\Resources\Visitors\Tables;

use App\Models\Visitor;
use App\Filament\Client\Resources\Visitors\VisitorResource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class VisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_visitors')
                    ->label('Total Visitors')
                    ->state(function ($record) {

                        return Visitor::query()
                            ->where('website_id', $record->id)
                            ->whereNotNull('visitor_uuid')
                            ->where('visitor_uuid', '!=', '')
                            ->distinct()
                            ->count('visitor_uuid');
                    }),

                TextColumn::make('total_visits')
                    ->label('Total Visits')
                    ->state(function ($record) {

                        $latestIds = Visitor::query()
                            ->where('website_id', $record->id)
                            ->whereNotNull('visitor_uuid')
                            ->where('visitor_uuid', '!=', '')
                            ->whereIn('id', function ($subQuery) use ($record) {

                                $subQuery
                                    ->from('visitors')
                                    ->selectRaw('MAX(id)')
                                    ->where('website_id', $record->id)
                                    ->whereNotNull('visitor_uuid')
                                    ->where('visitor_uuid', '!=', '')
                                    ->groupBy('visitor_uuid');

                            })
                            ->pluck('id');

                        return Visitor::query()
                            ->whereIn('id', $latestIds)
                            ->withCount('sessions')
                            ->get()
                            ->sum('sessions_count');
                    }),

            ])

            ->recordUrl(
                fn ($record) =>
                    VisitorResource::getUrl(
                        'website-visitors',
                        ['record' => $record]
                    )
            )

            ->defaultSort('name', 'asc');
    }
}