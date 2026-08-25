<?php

namespace App\Filament\Client\Resources\ChatMessages\Tables;

use App\Filament\Client\Resources\ChatMessages\ChatMessageResource;
use App\Models\ChatConversation;
use App\Models\Visitor;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('total_visitors')
                    ->label('Visitors')
                    ->state(
                        fn ($record) =>
                            Visitor::query()
                                ->where('website_id', $record->id)
                                ->whereNotNull('visitor_uuid')
                                ->where('visitor_uuid', '!=', '')
                                ->distinct()
                                ->count('visitor_uuid')
                    ),

                TextColumn::make('total_conversations')
                    ->label('Conversations')
                    ->state(
                        fn ($record) =>
                            ChatConversation::query()
                                ->where('website_id', $record->id)
                                ->count()
                    ),

            ])

            ->recordUrl(
                fn ($record) =>
                    ChatMessageResource::getUrl(
                        'website-visitors',
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
                            ChatMessageResource::getUrl(
                                'website-visitors',
                                [
                                    'website' => $record->id,
                                ]
                            )
                    ),
            ])

            ->defaultSort('name', 'asc');
    }
}