<?php

namespace App\Filament\Client\Resources\ChatbotFlows\Pages;

use App\Filament\Client\Resources\ChatbotFlows\ChatbotFlowResource;
use App\Models\Website;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListChatbotFlows extends ListRecords
{
    protected static string $resource = ChatbotFlowResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Chatbot Flow')
                ->visible(function (): bool {

                    $user = auth()->user();

                    if (
                        !$user ||
                        $user->role !== 'owner' ||
                        !$user->company_id
                    ) {
                        return false;
                    }

                    return Website::query()
                        ->where(
                            'company_id',
                            $user->company_id
                        )
                        ->whereDoesntHave('chatbotFlow')
                        ->exists();
                }),
        ];
    }
}