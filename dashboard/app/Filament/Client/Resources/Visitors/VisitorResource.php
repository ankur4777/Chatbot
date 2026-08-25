<?php

namespace App\Filament\Client\Resources\Visitors;

use App\Filament\Client\Resources\Visitors\Pages\ListVisitors;
use App\Filament\Client\Resources\Visitors\Pages\ManageVisitors;
use App\Filament\Client\Resources\Visitors\Tables\VisitorsTable;
use App\Models\Website;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class VisitorResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static ?string $navigationLabel = 'Visitors';

    protected static ?string $modelLabel = 'Visitor';

protected static ?string $pluralModelLabel = 'Visitors';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        if ($user && $user->role === 'owner' && $user->company_id) {
            return $query->where(
                'company_id',
                $user->company_id
            );
        }

        return $query->whereRaw('1 = 0');
    }

    public static function table(Table $table): Table
    {
        return VisitorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVisitors::route('/'),

            'website-visitors' => ManageVisitors::route(
                '/{record}/visitors'
            ),
        ];
    }
}