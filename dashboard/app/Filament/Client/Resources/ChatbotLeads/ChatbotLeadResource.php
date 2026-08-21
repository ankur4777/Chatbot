<?php

namespace App\Filament\Client\Resources\ChatbotLeads;

use App\Filament\Client\Resources\ChatbotLeads\Pages\EditChatbotLead;
use App\Filament\Client\Resources\ChatbotLeads\Pages\ListChatbotLeads;
use App\Filament\Client\Resources\ChatbotLeads\Schemas\ChatbotLeadForm;
use App\Filament\Client\Resources\ChatbotLeads\Tables\ChatbotLeadsTable;
use App\Models\ChatbotLead;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotLeadResource extends Resource
{
    protected static ?string $model = ChatbotLead::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->whereHas('website', function ($websiteQuery) use ($user) {
                $websiteQuery->where(
                    'company_id',
                    $user->company_id
                );
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return ChatbotLeadForm::configure($schema);
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
            'edit' => EditChatbotLead::route('/{record}/edit'),
        ];
    }
}