<?php

namespace App\Filament\Client\Resources\ChatbotFlowSteps;

use App\Filament\Client\Resources\ChatbotFlowSteps\Pages\CreateChatbotFlowStep;
use App\Filament\Client\Resources\ChatbotFlowSteps\Pages\EditChatbotFlowStep;
use App\Filament\Client\Resources\ChatbotFlowSteps\Pages\ListChatbotFlowSteps;
use App\Filament\Client\Resources\ChatbotFlowSteps\Schemas\ChatbotFlowStepForm;
use App\Filament\Client\Resources\ChatbotFlowSteps\Tables\ChatbotFlowStepsTable;
use App\Models\ChatbotFlowStep;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotFlowStepResource extends Resource
{
    protected static ?string $model = ChatbotFlowStep::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'question';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->whereHas('flow.website', function ($websiteQuery) use ($user) {
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
        return ChatbotFlowStepForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotFlowStepsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
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