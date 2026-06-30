<?php

namespace App\Filament\Resources\KnowledgeChunks\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class KnowledgeChunkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('knowledge_base_id')
                    ->relationship('knowledgeBase', 'title')
                    ->required(),
                Textarea::make('chunk_text')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('chunk_order')
                    ->required()
                    ->numeric(),
            ]);
    }
}
