<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadUsers')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('admin.users.download'))
                ->openUrlInNewTab(),
            CreateAction::make(),
        ];
    }
}
