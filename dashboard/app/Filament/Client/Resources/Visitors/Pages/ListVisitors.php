<?php

namespace App\Filament\Client\Resources\Visitors\Pages;

use App\Filament\Client\Resources\Visitors\VisitorResource;
use App\Filament\Client\Resources\Visitors\Widgets\VisitorStats;
use Filament\Resources\Pages\ListRecords;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            VisitorStats::class,
        ];
    }
}