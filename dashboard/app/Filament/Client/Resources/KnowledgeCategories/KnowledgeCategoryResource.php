<?php

namespace App\Filament\Client\Resources\KnowledgeCategories;

use App\Filament\Client\Resources\KnowledgeCategories\Pages\CreateKnowledgeCategory;
use App\Filament\Client\Resources\KnowledgeCategories\Pages\EditKnowledgeCategory;
use App\Filament\Client\Resources\KnowledgeCategories\Pages\ListKnowledgeCategories;
use App\Filament\Client\Resources\KnowledgeCategories\Pages\WebsiteCategories;
use App\Filament\Client\Resources\KnowledgeCategories\Schemas\KnowledgeCategoryForm;
use App\Filament\Client\Resources\KnowledgeCategories\Tables\KnowledgeCategoriesTable;
use App\Models\KnowledgeCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KnowledgeCategoryResource extends Resource
{
    protected static ?string $model = KnowledgeCategory::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->whereHas('website', function ($websiteQuery) use ($user) {
                $websiteQuery->where(
                    'company_id',
                    $user->company_id
                );
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return KnowledgeCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KnowledgeCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKnowledgeCategories::route('/'),

            'create' => CreateKnowledgeCategory::route('/create'),

            'edit' => EditKnowledgeCategory::route('/{record}/edit'),

            'website-categories' => WebsiteCategories::route(
                '/website/{website}/categories'
            ),
        ];
    }
}