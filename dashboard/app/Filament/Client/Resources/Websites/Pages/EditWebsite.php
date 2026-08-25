<?php

namespace App\Filament\Client\Resources\Websites\Pages;

use App\Filament\Client\Resources\Websites\WebsiteResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;

class EditWebsite extends EditRecord
{
    protected static string $resource = WebsiteResource::class;
    protected function getSaveFormAction(): Action
{
    return parent::getSaveFormAction()
        ->label('Save Changes');
}
}