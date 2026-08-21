<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class KnowledgeSourceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                Select::make('website_id')
                    ->label('Website')
                    ->relationship(
                        name: 'website',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query) {
                            $user = auth()->user();

                            if ($user && $user->role === 'owner') {
                                $query->where(
                                    'company_id',
                                    $user->company_id
                                );
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        // Website change hone par category reset
                        $set('knowledge_category_id', null);
                    })
                    ->required(),

                Select::make('knowledge_category_id')
                    ->label('Category')
                    ->relationship(
                        name: 'knowledgeCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query, Get $get) {

                            $websiteId = $get('website_id');

                            if ($websiteId) {
                                $query->where(
                                    'website_id',
                                    $websiteId
                                );
                            } else {
                                $query->whereRaw('1 = 0');
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('type')
                    ->label('Source Type')
                    ->options([
                        'website'  => 'Website',
                        'pdf'      => 'PDF',
                        'json'     => 'JSON',
                        'docx'     => 'DOCX',
                        'txt'      => 'TXT',
                        'faq'      => 'FAQ',
                        'manual'   => 'Manual',
                        'database' => 'Database',
                    ])
                    ->live()
                    ->afterStateUpdated(function (Set $set) {
                        // Type change hone par old URL/file remove
                        $set('source', null);
                    })
                    ->required(),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('source')
                    ->label('Website URL')
                    ->url()
                    ->visible(
                        fn (Get $get): bool =>
                            $get('type') === 'website'
                    )
                    ->required(
                        fn (Get $get): bool =>
                            $get('type') === 'website'
                    ),

                FileUpload::make('source')
                    ->label('Upload PDF')
                    ->disk('public')
                    ->directory('knowledge')
                    ->acceptedFileTypes([
                        'application/pdf',
                    ])
                    ->visible(
                        fn (Get $get): bool =>
                            $get('type') === 'pdf'
                    )
                    ->required(
                        fn (Get $get): bool =>
                            $get('type') === 'pdf'
                    ),

                Textarea::make('error')
                    ->label('Error Message')
                    ->disabled()
                    ->rows(3)
                    ->visible(
                        fn ($record): bool =>
                            $record?->status === 'failed'
                    )
                    ->columnSpanFull(),
            ]);
    }
}