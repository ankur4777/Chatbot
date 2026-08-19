<?php

namespace App\Filament\Client\Resources\KnowledgeSources;

use App\Filament\Client\Resources\KnowledgeSources\Pages\CreateKnowledgeSource;
use App\Filament\Client\Resources\KnowledgeSources\Pages\EditKnowledgeSource;
use App\Filament\Client\Resources\KnowledgeSources\Pages\ListKnowledgeSources;
use App\Filament\Client\Resources\KnowledgeSources\Pages\ViewKnowledgeSource;
use App\Filament\Client\Resources\KnowledgeSources\Schemas\KnowledgeSourceForm;
use App\Filament\Client\Resources\KnowledgeSources\Schemas\KnowledgeSourceInfolist;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Client\Resources\KnowledgeSources\Tables\KnowledgeSourcesTable;
use App\Models\KnowledgeSource;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KnowledgeSourceResource extends Resource
{
    protected static ?string $model = KnowledgeSource::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'title';
    public static function getEloquentQuery(): Builder
{
    $query = parent::getEloquentQuery();

    $user = auth()->user();

    // Owner can only see knowledge sources
    // belonging to websites of their company.
    if ($user && $user->role === 'owner' && $user->company_id) {
        return $query->whereHas('website', function ($websiteQuery) use ($user) {
            $websiteQuery->where('company_id', $user->company_id);
        });
    }

    // No company = no knowledge sources
    return $query->whereRaw('1 = 0');
}

    public static function form(Schema $schema): Schema
    {
        return KnowledgeSourceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return KnowledgeSourceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KnowledgeSourcesTable::configure($table);
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
            'index' => ListKnowledgeSources::route('/'),
            'create' => CreateKnowledgeSource::route('/create'),
            'view' => ViewKnowledgeSource::route('/{record}'),
            'edit' => EditKnowledgeSource::route('/{record}/edit'),
        ];
    }
}
