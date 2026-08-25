<?php

namespace App\Filament\Client\Resources\Websites;

use App\Filament\Client\Resources\Websites\Pages\EditWebsite;
use App\Filament\Client\Resources\Websites\Pages\ListWebsites;
use App\Filament\Client\Resources\Websites\Schemas\WebsiteForm;
use App\Filament\Client\Resources\Websites\Tables\WebsitesTable;
use App\Models\Website;
use BackedEnum;
use App\Support\BrowserTime;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteResource extends Resource
{
    protected static ?string $model = Website::class;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedGlobeAlt;

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

    public static function form(Schema $schema): Schema
    {
        return WebsiteForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsitesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsites::route('/'),
            'edit' => EditWebsite::route('/{record}/edit'),
        ];
    }
}