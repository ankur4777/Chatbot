<?php

namespace App\Filament\Client\Resources\Visitors\Widgets;

use App\Models\Visitor;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WebsiteVisitorStats extends StatsOverviewWidget
{
    public int $websiteId;

    protected function getStats(): array
    {
        $query = Visitor::query()
            ->where('website_id', $this->websiteId)
            ->whereNotNull('visitor_uuid')
            ->where('visitor_uuid', '!=', '');

        /*
        |--------------------------------------------------------------------------
        | Unique Visitors
        |--------------------------------------------------------------------------
        */

        $totalVisitors = (clone $query)
            ->distinct('visitor_uuid')
            ->count('visitor_uuid');

        /*
        |--------------------------------------------------------------------------
        | Total Visits
        |--------------------------------------------------------------------------
        */

        $visitorIds = (clone $query)
            ->whereIn('id', function ($subQuery) {

                $subQuery
                    ->from('visitors')
                    ->selectRaw('MAX(id)')
                    ->where('website_id', $this->websiteId)
                    ->whereNotNull('visitor_uuid')
                    ->where('visitor_uuid', '!=', '')
                    ->groupBy('visitor_uuid');

            })
            ->pluck('id');

        $totalVisits = Visitor::query()
            ->whereIn('id', $visitorIds)
            ->withCount('sessions')
            ->get()
            ->sum('sessions_count');

        return [
            Stat::make(
                'Total Visitors',
                number_format($totalVisitors)
            ),

            Stat::make(
                'Total Visits',
                number_format($totalVisits)
            ),
        ];
    }
}