<?php

namespace App\Filament\Client\Resources\ChatbotQuickReplies;

use App\Filament\Client\Resources\ChatbotQuickReplies\Pages\CreateChatbotQuickReply;
use App\Filament\Client\Resources\ChatbotQuickReplies\Pages\EditChatbotQuickReply;
use App\Filament\Client\Resources\ChatbotQuickReplies\Pages\ListChatbotQuickReplies;
use App\Filament\Client\Resources\ChatbotQuickReplies\Schemas\ChatbotQuickReplyForm;
use App\Filament\Client\Resources\ChatbotQuickReplies\Tables\ChatbotQuickRepliesTable;
use App\Models\ChatbotQuickReply;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatbotQuickReplyResource extends Resource
{
    protected static ?string $model = ChatbotQuickReply::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

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
        return ChatbotQuickReplyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatbotQuickRepliesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatbotQuickReplies::route('/'),
            'create' => CreateChatbotQuickReply::route('/create'),
            'edit' => EditChatbotQuickReply::route('/{record}/edit'),
        ];
    }
}