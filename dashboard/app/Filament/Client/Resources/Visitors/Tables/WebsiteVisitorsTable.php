<?php

namespace App\Filament\Client\Resources\Visitors\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Support\BrowserTime;
class WebsiteVisitorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {

                $query
                    ->whereNotNull('visitor_uuid')
                    ->where('visitor_uuid', '!=', '')
                    ->whereIn('id', function ($subQuery) {

                        $subQuery
                            ->from('visitors')
                            ->selectRaw('MAX(id)')
                            ->whereNotNull('visitor_uuid')
                            ->where('visitor_uuid', '!=', '')
                            ->groupBy('website_id', 'visitor_uuid');

                    });

            })

            ->columns([

                TextColumn::make('visitor_uuid')
    ->label('Visitor')
    ->formatStateUsing(
        fn ($state) => $state
            ? 'Visitor ' . substr($state, 0, 8)
            : '—'
    )
    ->tooltip(
        fn ($record) => $record->visitor_uuid
            ?: 'No Visitor ID'
    )
    ->copyable()
    ->copyableState(
        fn ($record) => $record->visitor_uuid
    )
    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable(),

                TextColumn::make('sessions_count')
                    ->label('Total Visits')
                    ->counts('sessions')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('First Seen')
                    ->date('d M Y')
                    ->tooltip(fn ($record) =>
                         BrowserTime::format(
        $record->created_at,
        'd M Y, h:i A'
    )
)
                    ->sortable(),

                TextColumn::make('last_activity_at')
                    ->label('Last Activity')
                    ->since()
                    ->tooltip(fn ($record) =>
                        BrowserTime::format(
        $record->last_activity_at,
        'd M Y, h:i A'
    )
                    )
                    ->sortable(),

            ])

            ->defaultSort(
                'last_activity_at',
                'desc'
            );
    }
}