<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Pages;

use App\Filament\Client\Resources\LiveChatAgents\AgentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAgent extends EditRecord
{
    protected static string $resource = AgentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        $data['role'] = 'agent';
        $data['availability_status'] ??= 'offline';
        $data['is_online'] = $data['availability_status'] === 'online';

        return $data;
    }
}
