<?php

namespace App\Filament\Client\Resources\ChatMessages;

use App\Filament\Client\Resources\ChatMessages\Pages\EditChatMessage;
use App\Filament\Client\Resources\ChatMessages\Pages\ListChatMessages;
use App\Filament\Client\Resources\ChatMessages\Schemas\ChatMessageForm;
use App\Filament\Client\Resources\ChatMessages\Tables\ChatMessagesTable;
use App\Models\ChatMessage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatMessageResource extends Resource
{
    protected static ?string $model = ChatMessage::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'id';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->whereHas('conversation.website', function ($websiteQuery) use ($user) {
                $websiteQuery->where('company_id', $user->company_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return ChatMessageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ChatMessagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListChatMessages::route('/'),
            'edit' => EditChatMessage::route('/{record}/edit'),
        ];
    }
}