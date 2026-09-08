<?php

namespace App\Filament\Client\Resources\LiveChatSettings;

use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Filament\Client\Resources\LiveChatSettings\Pages\EditLiveChatSetting;
use App\Filament\Client\Resources\LiveChatSettings\Pages\ListLiveChatSettings;
use App\Filament\Client\Resources\LiveChatSettings\Schemas\LiveChatSettingForm;
use App\Filament\Client\Resources\LiveChatSettings\Tables\LiveChatSettingsTable;
use App\Models\WebsiteSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LiveChatSettingResource extends Resource
{
    use RequiresLiveChatAccess;

    protected static ?string $model = WebsiteSetting::class;

    protected static ?string $navigationLabel = 'Live Chat Settings';

    protected static ?string $modelLabel = 'Live Chat Setting';

    protected static ?string $pluralModelLabel = 'Live Chat Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 70;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedCog6Tooth;

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (! $user?->hasLiveChatAccess()) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $user->company_id
                )
            );
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
