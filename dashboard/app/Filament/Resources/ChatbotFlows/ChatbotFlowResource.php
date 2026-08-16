<?php

namespace App\Filament\Resources\ChatbotFlows;

use App\Filament\Resources\ChatbotFlows\Pages\CreateChatbotFlow;
use App\Filament\Resources\ChatbotFlows\Pages\EditChatbotFlow;
use App\Filament\Resources\ChatbotFlows\Pages\ListChatbotFlows;
use App\Filament\Resources\ChatbotFlows\Schemas\ChatbotFlowForm;
use App\Filament\Resources\ChatbotFlows\Tables\ChatbotFlowsTable;
use App\Models\ChatbotFlow;
use BackedEnum;
use App\Filament\Resources\ChatbotFlows\RelationManagers\StepsRelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatbotFlowResource extends Resource
{
    protected static ?string $model = ChatbotFlow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

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
