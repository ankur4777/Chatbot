<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Schemas;

use App\Models\User;
use App\Models\WebsiteSetting;
use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class AgentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Phone Number')
                    ->tel()
                    ->required()
                    ->maxLength(30)
                    ->regex('/^\+?[0-9\s\-()]{7,30}$/'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->maxLength(255),

                Toggle::make('status')
                    ->label('Active')
                    ->default(true),

                Select::make('availability_status')
                    ->label('Availability')
                    ->options([
                        'online' => 'Online',
                        'away' => 'On Break',
                        'offline' => 'Offline',
                    ])
                    ->default('offline')
                    ->required(),

                Select::make('assignedWebsites')
                    ->label('Websites')
                    ->relationship(
                        name: 'assignedWebsites',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) =>
                            $query->where(
                                'company_id',
                                auth()->user()->company_id
                            )
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->required()
                    ->rules([
                        fn (?User $record): Closure =>
                            function (string $attribute, $value, Closure $fail) use ($record): void {
                                self::validateWebsiteAgentLimits(
                                    is_array($value) ? $value : [],
                                    $record,
                                    $fail
                                );
                            },
                    ])
                    ->helperText('Select which websites this agent can handle.'),
            ]);
    }

    protected static function validateWebsiteAgentLimits(
        array $websiteIds,
        ?User $record,
        Closure $fail
    ): void {
        $companyId = auth()->user()?->company_id;
        $agentId = $record?->id;

        collect($websiteIds)
            ->filter()
            ->map(fn ($websiteId): int => (int) $websiteId)
            ->unique()
            ->each(function (int $websiteId) use ($companyId, $agentId, $fail): void {
                $setting = WebsiteSetting::query()
                    ->with('website:id,name,company_id')
                    ->where('website_id', $websiteId)
                    ->whereHas(
                        'website',
                        fn ($query) => $query->where('company_id', $companyId)
                    )
                    ->first();

                $limit = (int) ($setting?->max_agents_per_website ?? 0);

                if ($limit < 1) {
                    return;
                }

                $assignedAgents = DB::table('website_agent')
                    ->join('users', 'users.id', '=', 'website_agent.agent_id')
                    ->where('website_agent.website_id', $websiteId)
                    ->where('users.company_id', $companyId)
                    ->where('users.role', 'agent')
                    ->when(
                        $agentId,
                        fn ($query) => $query->where('users.id', '!=', $agentId)
                    )
                    ->count();

                if ($assignedAgents >= $limit) {
                    $websiteName = $setting?->website?->name ?? 'this website';

                    $fail("{$websiteName} already has the maximum allowed {$limit} agents.");
                }
            });
    }
}
