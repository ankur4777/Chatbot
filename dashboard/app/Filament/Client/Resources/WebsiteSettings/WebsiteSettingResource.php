<?php

namespace App\Filament\Client\Resources\WebsiteSettings;

use App\Filament\Client\Resources\WebsiteSettings\Pages\CreateWebsiteSetting;
use App\Filament\Client\Resources\WebsiteSettings\Pages\EditWebsiteSetting;
use App\Filament\Client\Resources\WebsiteSettings\Pages\ListWebsiteSettings;
use App\Filament\Client\Resources\WebsiteSettings\Schemas\WebsiteSettingForm;
use App\Filament\Client\Resources\WebsiteSettings\Tables\WebsiteSettingsTable;
use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WebsiteSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static ?string $navigationLabel = 'Chatbot Settings';

    protected static ?string $modelLabel = 'Chatbot Setting';

    protected static ?string $pluralModelLabel = 'Chatbot Settings';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Chatbot';

    protected static ?int $navigationSort = 80;

    protected static ?string $recordTitleAttribute = null;

    public static function getRecordTitle(?Model $record): ?string
    {
        return $record?->website?->name ?? static::getModelLabel();
    }

    public static function hasRecordTitle(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return WebsiteSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WebsiteSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWebsiteSettings::route('/'),
            'create' => CreateWebsiteSetting::route('/create'),
            'edit' => EditWebsiteSetting::route('/{record}/edit'),
        ];
    }

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
}
