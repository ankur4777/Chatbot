<?php

namespace App\Filament\Resources\ChatbotQuickReplies;

use App\Filament\Resources\ChatbotQuickReplies\Pages\CreateChatbotQuickReply;
use App\Filament\Resources\ChatbotQuickReplies\Pages\EditChatbotQuickReply;
use App\Filament\Resources\ChatbotQuickReplies\Pages\ListChatbotQuickReplies;
use App\Filament\Resources\ChatbotQuickReplies\Schemas\ChatbotQuickReplyForm;
use App\Filament\Resources\ChatbotQuickReplies\Tables\ChatbotQuickRepliesTable;
use App\Models\ChatbotQuickReply;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ChatbotQuickReplyResource extends Resource
{
    protected static ?string $model = ChatbotQuickReply::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'label';

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
        return [
            //
        ];
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
