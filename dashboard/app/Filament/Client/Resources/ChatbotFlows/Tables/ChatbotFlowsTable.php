<?php

namespace App\Filament\Client\Resources\ChatbotFlows\Tables;

use Filament\Actions\DeleteAction;
use App\Support\BrowserTime;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChatbotFlowsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Flow')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('steps_count')
                    ->label('Steps')
                    ->counts('steps')
                    ->formatStateUsing(
                        fn ($state) => $state . ' ' . ($state == 1 ? 'Step' : 'Steps')
                    )
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->updated_at
                                ? BrowserTime::format(
                $record->updated_at,
                'd M Y, h:i A'
            )
                                : 'N/A'
                    )
                    ->sortable(),

            ])

            ->recordActions([
                EditAction::make()
                    ->label('Manage'),

                DeleteAction::make()
                    ->requiresConfirmation(),
            ]);
    }
}