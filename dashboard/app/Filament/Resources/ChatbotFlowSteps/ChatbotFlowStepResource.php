<?php

namespace App\Filament\Resources\ChatbotFlowSteps;

use App\Filament\Resources\ChatbotFlowSteps\Pages\CreateChatbotFlowStep;
use App\Filament\Resources\ChatbotFlowSteps\Pages\EditChatbotFlowStep;
use App\Filament\Resources\ChatbotFlowSteps\Pages\ListChatbotFlowSteps;
use App\Filament\Resources\ChatbotFlowSteps\Schemas\ChatbotFlowStepForm;
use App\Filament\Resources\ChatbotFlowSteps\Tables\ChatbotFlowStepsTable;
use App\Models\ChatbotFlowStep;
use BackedEnum;
use App\Filament\Resources\ChatbotFlowSteps\RelationManagers\OptionsRelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatbotFlowStepResource extends Resource
{
    protected static ?string $model = ChatbotFlowStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'question';

    public static function form(Schema $schema): Schema
    {
        return ChatbotFlowStepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotFlowStepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            OptionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotFlowSteps::route('/'),
            'create' => CreateChatbotFlowStep::route('/create'),
            'edit' => EditChatbotFlowStep::route('/{record}/edit'),
        ];
    }
}
