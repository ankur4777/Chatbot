<?php

namespace App\Filament\Client\Resources\ChatConversations\Tables;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use Filament\Actions\Action;
use App\Support\BrowserTime;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('conversations_count')
                    ->label('Total Conversations')
                    ->sortable(),

                TextColumn::make('conversations_max_updated_at')
                    ->label('Last Activity')
                    ->since()
                    ->placeholder('No conversations')
                    ->tooltip(
    fn ($record) =>
        $record->conversations_max_updated_at
            ? BrowserTime::format(
                $record->conversations_max_updated_at,
                'd M Y, h:i A'
            )
            : 'No conversations'
)
                    ->sortable(),
            ])

            ->recordUrl(
                fn ($record) =>
                    ChatConversationResource::getUrl(
                        'website-conversations',
                        [
                            'website' => $record->id,
                        ]
                    )
            )

            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            ChatConversationResource::getUrl(
                                'website-conversations',
                                [
                                    'website' => $record->id,
                                ]
                            )
                    ),
            ])

            ->defaultSort('name', 'asc');
    }
}