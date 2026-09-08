<?php

namespace App\Filament\Client\Resources\LiveChatAgents;

use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Filament\Client\Resources\LiveChatAgents\Pages\CreateAgent;
use App\Filament\Client\Resources\LiveChatAgents\Pages\EditAgent;
use App\Filament\Client\Resources\LiveChatAgents\Pages\ListAgents;
use App\Filament\Client\Resources\LiveChatAgents\Schemas\AgentForm;
use App\Filament\Client\Resources\LiveChatAgents\Tables\AgentsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgentResource extends Resource
{
    use RequiresLiveChatAccess;

    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Agents';

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?string $slug = 'agents';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 50;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        if (! $user?->hasLiveChatAccess()) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return parent::getEloquentQuery()
            ->with('assignedWebsites')
            ->where('role', 'agent')
            ->where('company_id', $user->company_id);
    }

    public static function form(Schema $schema): Schema
    {
        return AgentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AgentsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'edit' => EditAgent::route('/{record}/edit'),
        ];
    }
}
