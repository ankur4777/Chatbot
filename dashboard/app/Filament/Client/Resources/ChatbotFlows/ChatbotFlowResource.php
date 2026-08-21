<?php

namespace App\Filament\Client\Resources\ChatbotFlows;

use App\Filament\Client\Resources\ChatbotFlows\Pages\CreateChatbotFlow;
use App\Filament\Client\Resources\ChatbotFlows\Pages\EditChatbotFlow;
use App\Filament\Client\Resources\ChatbotFlows\Pages\ListChatbotFlows;
use App\Filament\Client\Resources\ChatbotFlows\RelationManagers\StepsRelationManager;
use App\Filament\Client\Resources\ChatbotFlows\Schemas\ChatbotFlowForm;
use App\Filament\Client\Resources\ChatbotFlows\Tables\ChatbotFlowsTable;
use App\Models\ChatbotFlow;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotFlowResource extends Resource
{
    protected static ?string $model = ChatbotFlow::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->whereHas('website', function ($websiteQuery) use ($user) {
                $websiteQuery->where('company_id', $user->company_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return ChatbotFlowForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotFlowsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StepsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotFlows::route('/'),
            'create' => CreateChatbotFlow::route('/create'),
            'edit' => EditChatbotFlow::route('/{record}/edit'),
        ];
    }
}