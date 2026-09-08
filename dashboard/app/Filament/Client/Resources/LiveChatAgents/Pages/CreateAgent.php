<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Pages;

use App\Filament\Client\Resources\LiveChatAgents\AgentResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateAgent extends CreateRecord
{
    protected static string $resource = AgentResource::class;

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()->hidden();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['role'] = 'agent';
        $data['availability_status'] ??= 'offline';
        $data['is_online'] = $data['availability_status'] === 'online';

        return $data;
    }
}
