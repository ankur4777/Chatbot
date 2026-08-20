<?php

namespace App\Filament\Client\Resources\KnowledgeCategories\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class KnowledgeCategoryForm
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
                                $query->where('company_id', $user->company_id);
                            }
                        }
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('name')
                    ->label('Category Name')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('e.g. Products, Services, FAQs'),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->maxLength(1000)
                    ->placeholder('Describe what type of knowledge belongs to this category...')
                    ->columnSpanFull(),

            ]);
    }
}