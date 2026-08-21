<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class KnowledgeSourceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Knowledge Source Information')
                    ->schema([
                        TextEntry::make('title')
                            ->label('Title'),

                        TextEntry::make('type')
                            ->label('Source Type')
                            ->badge(),

                        TextEntry::make('knowledgeCategory.name')
                            ->label('Category'),

                        TextEntry::make('website.name')
                            ->label('Website'),
                    ])
                    ->columns(2),

                Section::make('Sync Information')
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('pages')
                            ->label('Pages'),

                        TextEntry::make('chunks')
                            ->label('Chunks'),

                        TextEntry::make('last_synced_at')
                            ->label('Last Synced')
                            ->dateTime(),

                        TextEntry::make('error')
                            ->label('Error')
                            ->placeholder('No error'),
                    ])
                    ->columns(2),

                Section::make('Source File')
                    ->schema([
                        TextEntry::make('source')
                            ->label('File')
                            ->placeholder('No file'),
                    ]),
            ]);
    }
}