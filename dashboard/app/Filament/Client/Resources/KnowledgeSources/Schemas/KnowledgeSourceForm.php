<?php

namespace App\Filament\Client\Resources\KnowledgeSources\Schemas;

use Filament\Forms\Components\FileUpload;
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
                    ->required(),

                Select::make('knowledge_category_id')
                    ->label('Category')
                    ->relationship(
                        name: 'knowledgeCategory',
                        titleAttribute: 'name',
                        modifyQueryUsing: function ($query, $get) {

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

                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                FileUpload::make('source')
                    ->label('Upload PDF')
                    ->disk('public')
                    ->directory('knowledge')
                    ->preserveFilenames()
                    ->acceptedFileTypes([
                        'application/pdf',
                    ])
                    ->required(),

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