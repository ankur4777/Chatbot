<?php

namespace App\Filament\Resources\KnowledgeSources\Schemas;

use App\Models\Website;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

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

                Select::make('type')
                    ->options([
                        'website' => 'Website',
                        'pdf' => 'PDF',
                        'json' => 'JSON',
                        'docx' => 'DOCX',
                        'txt' => 'TXT',
                        'faq' => 'FAQ',
                    ])
                    ->required(),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('source')
                    ->rows(4),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->default('pending')
                    ->disabled(),

                TextInput::make('pages')
                    ->numeric()
                    ->disabled(),

                TextInput::make('chunks')
                    ->numeric()
                    ->disabled(),

            ]);
    }
}