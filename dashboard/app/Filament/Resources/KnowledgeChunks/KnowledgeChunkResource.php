<?php

namespace App\Filament\Resources\KnowledgeChunks;

use App\Filament\Resources\KnowledgeChunks\Pages\CreateKnowledgeChunk;
use App\Filament\Resources\KnowledgeChunks\Pages\EditKnowledgeChunk;
use App\Filament\Resources\KnowledgeChunks\Pages\ListKnowledgeChunks;
use App\Filament\Resources\KnowledgeChunks\Schemas\KnowledgeChunkForm;
use App\Filament\Resources\KnowledgeChunks\Tables\KnowledgeChunksTable;
use App\Models\KnowledgeChunk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KnowledgeChunkResource extends Resource
{
    protected static ?string $model = KnowledgeChunk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'chunk_order';

    public static function form(Schema $schema): Schema
    {
        return KnowledgeChunkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KnowledgeChunksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeChunks::route('/'),
            'create' => CreateKnowledgeChunk::route('/create'),
            'edit' => EditKnowledgeChunk::route('/{record}/edit'),
        ];
    }
}
