<?php

namespace App\Filament\Resources\Websites\Pages;

use App\Filament\Resources\Websites\WebsiteResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWebsites extends ListRecords
{
    protected static string $resource = WebsiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadWebsites')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('admin.websites.download'))
                ->openUrlInNewTab(),
            CreateAction::make()
             ->label('New Website'),
        ];
    }
}
