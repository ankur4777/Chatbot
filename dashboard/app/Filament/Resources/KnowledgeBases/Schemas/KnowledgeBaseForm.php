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
                TextInput::make('knowledge_category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('title')
                    ->required(),
                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),
                Select::make('source_type')
                    ->options([
            'text' => 'Text',
            'pdf' => 'Pdf',
            'url' => 'Url',
            'faq' => 'Faq',
            'docx' => 'Docx',
            'csv' => 'Csv',
            'json' => 'Json',
        ])
                    ->default('text')
                    ->required(),
                TextInput::make('source_file')
                    ->default(null),
                TextInput::make('source_url')
                    ->url()
                    ->default(null),
            ]);
    }
}
