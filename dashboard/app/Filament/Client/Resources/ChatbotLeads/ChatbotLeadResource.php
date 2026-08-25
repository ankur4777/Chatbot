<?php

namespace App\Filament\Client\Resources\ChatbotLeads;

use App\Filament\Client\Resources\ChatbotLeads\Pages\ListChatbotLeads;
use App\Filament\Client\Resources\ChatbotLeads\Pages\ManageChatbotLeads;
use App\Filament\Client\Resources\ChatbotLeads\Tables\ChatbotLeadsTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotLeadResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationLabel = 'Chatbot Leads';

    protected static ?string $modelLabel = 'Chatbot Lead';

    protected static ?string $pluralModelLabel = 'Chatbot Leads';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query
                ->where('company_id', $user->company_id)
                ->withCount('chatbotLeads')
                ->withCount([
                    'chatbotLeads as today_leads_count' => function ($query) {
                        $query->whereDate('created_at', today());
                    },
                ])
                ->withMax('chatbotLeads', 'created_at');
        }

        return $query->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return ChatbotLeadsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotLeads::route('/'),

            'website-leads' => ManageChatbotLeads::route(
                '/website/{website}/leads'
            ),
        ];
    }
}