<?php

namespace App\Filament\Client\Resources\Visitors\Widgets;

use App\Models\Visitor;
use App\Models\Website;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VisitorStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();

        if (! $user || $user->role !== 'owner' || ! $user->company_id) {
            return [
                Stat::make('Total Visitors', '0'),
                Stat::make('Total Visits', '0'),
            ];
        }

        $websiteIds = Website::query()
            ->where('company_id', $user->company_id)
            ->pluck('id');

        /*
        |--------------------------------------------------------------------------
        | Total Visitors
        |--------------------------------------------------------------------------
        */

        $totalVisitors = Visitor::query()
            ->whereIn('website_id', $websiteIds)
            ->whereNotNull('visitor_uuid')
            ->where('visitor_uuid', '!=', '')
            ->select('website_id', 'visitor_uuid')
            ->distinct()
            ->get()
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Total Visits
        |--------------------------------------------------------------------------
        */

        $latestIds = Visitor::query()
            ->whereIn('website_id', $websiteIds)
            ->whereNotNull('visitor_uuid')
            ->where('visitor_uuid', '!=', '')
            ->whereIn('id', function ($subQuery) use ($websiteIds) {

                $subQuery
                    ->from('visitors')
                    ->selectRaw('MAX(id)')
                    ->whereIn('website_id', $websiteIds)
                    ->whereNotNull('visitor_uuid')
                    ->where('visitor_uuid', '!=', '')
                    ->groupBy('website_id', 'visitor_uuid');

            })
            ->pluck('id');

        $totalVisits = Visitor::query()
            ->whereIn('id', $latestIds)
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