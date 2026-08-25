<?php

namespace App\Filament\Client\Resources\ChatMessages;

use App\Filament\Client\Resources\ChatMessages\Pages\ListChatMessages;
use App\Filament\Client\Resources\ChatMessages\Pages\WebsiteVisitors;
use App\Filament\Client\Resources\ChatMessages\Pages\VisitorConversations;
use App\Filament\Client\Resources\ChatMessages\Tables\ChatMessagesTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ChatMessageResource extends Resource

{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Website::class;

    protected static ?string $navigationLabel = 'Chat Messages';

    protected static ?string $modelLabel = 'Chat Message';

    protected static ?string $pluralModelLabel = 'Chat Messages';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

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
            return $query->where(
                'company_id',
                $user->company_id
            );
        }

        return $query->whereRaw('1 = 0');
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

            'website-visitors' => WebsiteVisitors::route(
                '/website/{website}/visitors'
            ),

            'visitor-conversations' => VisitorConversations::route(
                '/website/{website}/visitor/{visitor}/conversations'
            ),
        ];
    }
}