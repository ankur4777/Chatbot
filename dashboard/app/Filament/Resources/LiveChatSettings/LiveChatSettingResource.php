<?php

namespace App\Filament\Resources\LiveChatSettings;

use App\Filament\Resources\LiveChatSettings\Pages\EditLiveChatSetting;
use App\Filament\Resources\LiveChatSettings\Pages\ListLiveChatSettings;
use App\Filament\Resources\LiveChatSettings\Schemas\LiveChatSettingForm;
use App\Filament\Resources\LiveChatSettings\Tables\LiveChatSettingsTable;
use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LiveChatSettingResource extends Resource
{
    protected static ?string $model = WebsiteSetting::class;

    protected static ?string $navigationLabel = 'Live Chat Settings';

    protected static ?string $modelLabel = 'Live Chat Setting';

    protected static ?string $pluralModelLabel = 'Live Chat Settings';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCog6Tooth;

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()?->role === 'super_admin') {
            return $query;
        }

        return $query->whereRaw('1 = 0');
    }

    public static function form(Schema $schema): Schema
    {
        return LiveChatSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LiveChatSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLiveChatSettings::route('/'),
            'edit' => EditLiveChatSetting::route('/{record}/edit'),
        ];
    }
}
