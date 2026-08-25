<?php

namespace App\Filament\Client\Resources\ChatbotLeads\Tables;

use App\Filament\Client\Resources\ChatbotLeads\ChatbotLeadResource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotLeadsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('chatbot_leads_count')
                    ->label('Total Leads')
                    ->sortable(),

                TextColumn::make('today_leads_count')
                    ->label('Today')
                    ->sortable(),

                TextColumn::make('chatbot_leads_max_created_at')
                    ->label('Last Lead')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->chatbot_leads_max_created_at
                                ? \Carbon\Carbon::parse(
                                    $record->chatbot_leads_max_created_at
                                )->format('d M Y, h:i A')
                                : 'No leads'
                    )
                    ->placeholder('No leads')
                    ->sortable(),

            ])

            ->recordUrl(
                fn ($record) =>
                    ChatbotLeadResource::getUrl(
                        'website-leads',
                        [
                            'website' => $record->id,
                        ]
                    )
            )

            ->recordActions([

                Action::make('viewLeads')
                    ->label('View')
                    ->icon('heroicon-m-eye')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            ChatbotLeadResource::getUrl(
                                'website-leads',
                                [
                                    'website' => $record->id,
                                ]
                            )
                    ),

            ]);
    }
}