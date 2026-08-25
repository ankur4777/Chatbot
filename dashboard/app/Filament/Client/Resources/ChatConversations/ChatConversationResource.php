<?php

namespace App\Filament\Client\Resources\ChatConversations;

use App\Filament\Client\Resources\ChatConversations\Pages\ConversationMessages;
use App\Filament\Client\Resources\ChatConversations\Pages\ListChatConversations;
use App\Filament\Client\Resources\ChatConversations\Pages\WebsiteConversations;
use App\Filament\Client\Resources\ChatConversations\Tables\ChatConversationsTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatConversationResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationLabel = 'Conversations';

    protected static ?string $modelLabel = 'Conversation';

    protected static ?string $pluralModelLabel = 'Conversations';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if (
            $user &&
            $user->role === 'owner' &&
            $user->company_id
        ) {
            return $query
                ->where('company_id', $user->company_id)
                ->withCount('conversations')
                ->withMax('conversations', 'updated_at');
        }

        return $query->whereRaw('1 = 0');
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

            'website-conversations' => WebsiteConversations::route(
                '/website/{website}/conversations'
            ),

            'conversation-messages' => ConversationMessages::route(
                '/website/{website}/conversation/{conversation}/messages'
            ),
        ];
    }
}