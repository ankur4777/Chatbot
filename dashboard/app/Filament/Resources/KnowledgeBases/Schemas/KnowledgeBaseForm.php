<?php

namespace App\Filament\Resources\KnowledgeBases\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class KnowledgeBaseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('knowledge_category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(255),
                Textarea::make('content')
                    ->label('Content')
                    ->rows(10)
                    ->required()
                    ->columnSpanFull(),
                Select::make('source_type')
                    ->label('Source Type')
                    ->options([
                    'text' => 'Text',
                    'pdf' => 'PDF',
                    'url' => 'URL',
                    'faq' => 'FAQ',
                    'docx' => 'DOCX',
                    'csv' => 'CSV',
                    'json' => 'JSON',
                    ])
                    ->default('text')
                    ->required(),
                TextInput::make('source_file')
                    ->label('Source File')
                    ->maxLength(255),
                TextInput::make('source_url')
                    ->label('Source URL')
                    ->url()
                    ->maxLength(500),
            ]);
    }
}
