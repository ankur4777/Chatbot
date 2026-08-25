<?php

namespace App\Filament\Client\Resources\Visitors\Pages;

use App\Filament\Client\Resources\Visitors\VisitorResource;
use App\Filament\Client\Resources\Visitors\Tables\WebsiteVisitorsTable;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;
use App\Filament\Client\Resources\Visitors\Widgets\WebsiteVisitorStats;

class ManageVisitors extends ManageRelatedRecords
{
    protected static string $resource = VisitorResource::class;

    protected static string $relationship = 'visitors';

    public function table(Table $table): Table
    {
        return WebsiteVisitorsTable::configure($table);
    }

    public function getTitle(): string
    {
        return $this->getRecord()->name . ' Visitors';
    }

    protected function getHeaderWidgets(): array
{
    return [
        WebsiteVisitorStats::make([
            'websiteId' => $this->getRecord()->id,
        ]),
    ];
}
}