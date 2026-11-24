<?php

namespace App\Filament\Resources\Companies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use App\Support\BrowserTime;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                
                TextColumn::make('legal_name')
                    ->label('Legal Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('owner_name')
    ->label('Owner')
    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('country_code')
                    ->label('Country Code')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('subscription_expires_at')
                    ->label('Subscription')
                    ->dateTime('d M Y, h:i A')
                    ->placeholder('No expiry')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('websites.name')
    ->label('Websites')
    ->listWithLineBreaks()
    ->bulleted()
    ->limitList(3)
    ->expandableLimitedList(),

    TextColumn::make('gst_number')
    ->label('GST No.')
    ->searchable(),

TextColumn::make('address')
    ->label('Address')
    ->limit(40)
    ->tooltip(fn ($record) => $record->address),

                ToggleColumn::make('status')
                    ->label('Status')
                    ->sortable(),

                TextColumn::make('created_at')
    ->label('Created At')
    ->date('d M Y')
    ->sortable()
    ->tooltip(
        fn ($record) =>
            $record->created_at
                ? BrowserTime::format(
                    $record->created_at,
                    'd M Y, h:i A'
                )
                : 'N/A'
    ),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                 ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
