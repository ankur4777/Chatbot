<?php

namespace App\Filament\Resources\KnowledgeSources\Schemas;

use App\Models\Website;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\FileUpload;

class KnowledgeSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('website_id')
                    ->label('Website')
                    ->relationship('website', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('knowledge_category_id')
    ->label('Category')
    ->relationship('knowledgeCategory', 'name')
    ->searchable()
    ->preload()
    ->required(),

                Select::make('type')
                    ->options([
                        'website' => 'Website',
                        'pdf' => 'PDF',
                        'json' => 'JSON',
                        'docx' => 'DOCX',
                        'txt' => 'TXT',
                        'faq' => 'FAQ',
                        'manual' => 'Manual',
                        'database' => 'Database',
                    ])
                    ->live()
                    ->required(),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('source')
                    ->label('Website URL')
                    ->url()
                    ->visible(fn (Get $get): bool => $get('type') === 'website')
                    ->required(fn (Get $get): bool => $get('type') === 'website'),
                
                FileUpload::make('source')
                    ->label('Upload PDF')
                    ->disk('public')
                    ->directory('knowledge')
                    ->acceptedFileTypes(['application/pdf'])
                    ->visible(fn (Get $get): bool => $get('type') === 'pdf')
                    ->required(fn (Get $get): bool => $get('type') === 'pdf'),
                
                Textarea::make('error')
    ->label('Error Message')
    ->disabled()
    ->rows(3)
    ->visible(fn ($record): bool => $record?->status === 'failed')
    ->columnSpanFull(),
            ]);
    }
}