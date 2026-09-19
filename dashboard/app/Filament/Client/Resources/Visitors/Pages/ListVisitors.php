<?php

namespace App\Filament\Client\Resources\Visitors\Pages;

use App\Filament\Client\Resources\Visitors\VisitorResource;
use App\Filament\Client\Resources\Visitors\Widgets\VisitorStats;
use Filament\Actions\Action;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadVisitors')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route('client.visitors.download')),
        ];
    }
}
