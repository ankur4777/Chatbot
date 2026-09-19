<?php

namespace App\Filament\Client\Resources\Websites\Pages;

use App\Filament\Client\Resources\Websites\WebsiteResource;
use Filament\Actions\Action;
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
                ->color('gray')
                ->url(fn (): string => route('client.websites.download')),
        ];
    }
}
