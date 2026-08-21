<?php

namespace App\Filament\Client\Resources\ChatConversations;

use App\Filament\Client\Resources\ChatConversations\Pages\EditChatConversation;
use App\Filament\Client\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Client\Resources\ChatConversations\Schemas\ChatConversationForm;
use App\Filament\Client\Resources\ChatConversations\Tables\ChatConversationsTable;
use App\Models\ChatConversation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatConversationResource extends Resource
{
    protected static ?string $model = ChatConversation::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

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
        return ChatConversationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatConversationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatConversations::route('/'),
            'edit' => EditChatConversation::route('/{record}/edit'),
        ];
    }
}