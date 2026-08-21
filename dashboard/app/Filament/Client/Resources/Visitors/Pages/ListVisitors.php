<?php

namespace App\Filament\Client\Resources\Visitors\Pages;

use App\Filament\Client\Resources\Visitors\VisitorResource;
use Filament\Resources\Pages\ListRecords;

class ListVisitors extends ListRecords
{
    protected static string $resource = VisitorResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}