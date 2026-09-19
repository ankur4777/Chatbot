<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Tables;

use App\Models\User;
use App\Models\WebsiteSetting;
use App\Support\BrowserTime;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\URL;

class AgentsTable
{
    protected static array $capacityLabels = [];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $companyId = auth()->user()?->company_id;

                return self::applyAverageRatingAggregates($query->withCount([
                    'assignedConversations as active_live_chats_count' => fn (Builder $query) =>
                        $query
                            ->where('status', 'live_active')
                            ->whereHas(
                                'website',
                                fn (Builder $query) =>
                                    $query->where('company_id', $companyId)
                            ),
                ]));
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('assignedWebsites.name')
                    ->label('Websites')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('No website assigned'),

                IconColumn::make('status')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('availability_status')
                    ->label('Availability')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string => match ($state) {
                            'online' => 'Online',
                            'away' => 'On Break',
                            default => 'Offline',
                        }
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'online' => 'success',
                            'away' => 'warning',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('active_live_chats_count')
                    ->label('Active Chats')
                    ->state(
                        fn (User $record): string =>
                            ((int) ($record->active_live_chats_count ?? 0))
                            . ' / '
                            . self::maxActiveChatsCapacityLabel()
                    ),

                TextColumn::make('submitted_rating_average')
                    ->label('Avg. Rating')
                    ->state(fn (User $record): string => self::formatAverageRating($record))
                    ->badge()
                    ->color(fn (User $record): string => self::averageRatingColor($record)),

                TextColumn::make('last_seen_at')
                    ->label('Last Seen')
                    ->state(fn (User $record): string => match (true) {
                        $record->availability_status === 'online' => 'Active now',
                        blank($record->last_seen_at) => 'Never',
                        default => $record->last_seen_at->diffForHumans(),
                    })
                    ->tooltip(
                        fn (User $record): ?string =>
                            $record->last_seen_at
                                ? BrowserTime::format($record->last_seen_at)
                                : null
                    )
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->since()
                    ->tooltip(fn (User $record): string => BrowserTime::format($record->created_at))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('website')
                    ->label('Website')
                    ->options(fn (): array => \App\Models\Website::query()
                        ->where('company_id', auth()->user()->company_id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->query(
                        fn (Builder $query, array $data): Builder =>
                            self::applyAverageRatingAggregates(
                                filled($data['value'] ?? null)
                                    ? $query->whereHas(
                                        'assignedWebsites',
                                        fn (Builder $query) => $query
                                            ->whereKey((int) $data['value'])
                                            ->where(
                                                'company_id',
                                                auth()->user()->company_id
                                            )
                                    )
                                    : $query,
                                filled($data['value'] ?? null)
                                    ? (int) $data['value']
                                    : null
                            )
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('viewAgentDashboard')
                    ->label('View Agent Dashboard')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->visible(
                        fn (User $record): bool =>
                            $record->role === 'agent'
                            && $record->status
                            && $record->company_id === auth()->user()?->company_id
                            && (bool) $record->company?->status
                    )
                    ->url(
                        fn (User $record): string => URL::temporarySignedRoute(
                            'client.agents.view-agent-dashboard',
                            now()->addMinutes(5),
                            ['user' => $record],
                            false
                        )
                    ),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (User $record): bool =>
                    $record->role === 'agent'
                    && $record->company_id === auth()->user()->company_id
            );
    }

    protected static function applyAverageRatingAggregates(
        Builder $query,
        ?int $websiteId = null
    ): Builder {
        $companyId = auth()->user()?->company_id;

        return $query
            ->withAvg([
                'liveChatSessions as submitted_rating_average' =>
                    fn (Builder $query) => self::submittedRatingScope(
                        $query,
                        $companyId,
                        $websiteId
                    ),
            ], 'rating')
            ->withCount([
                'liveChatSessions as submitted_rating_count' =>
                    fn (Builder $query) => self::submittedRatingScope(
                        $query,
                        $companyId,
                        $websiteId
                    ),
            ]);
    }

    protected static function submittedRatingScope(
        Builder $query,
        ?int $companyId,
        ?int $websiteId = null
    ): Builder {
        return $query
            ->whereNotNull('ended_at')
            ->where('rating_status', 'submitted')
            ->whereNotNull('rating')
            ->whereHas(
                'conversation.website',
                fn (Builder $query) => $query
                    ->where('company_id', $companyId)
                    ->when(
                        $websiteId,
                        fn (Builder $query) => $query->whereKey($websiteId)
                    )
            );
    }

    protected static function formatAverageRating(User $record): string
    {
        $count = (int) ($record->submitted_rating_count ?? 0);

        if ($count < 1 || $record->submitted_rating_average === null) {
            return 'Not Rated';
        }

        return '★ '
            . number_format((float) $record->submitted_rating_average, 1)
            . " ({$count})";
    }

    protected static function averageRatingColor(User $record): string
    {
        if (
            (int) ($record->submitted_rating_count ?? 0) < 1
            || $record->submitted_rating_average === null
        ) {
            return 'gray';
        }

        $average = (float) $record->submitted_rating_average;

        return match (true) {
            $average < 3 => 'danger',
            $average < 4 => 'warning',
            default => 'success',
        };
    }

    protected static function maxActiveChatsCapacityLabel(): string
    {
        $companyId = auth()->user()?->company_id;

        if (! $companyId) {
            return '3';
        }

        if (array_key_exists($companyId, self::$capacityLabels)) {
            return self::$capacityLabels[$companyId];
        }

        $limits = WebsiteSetting::query()
            ->whereHas(
                'website',
                fn (Builder $query) => $query->where('company_id', $companyId)
            )
            ->pluck('max_active_chats_per_agent')
            ->map(fn ($limit): int => max(1, (int) ($limit ?: 3)))
            ->unique()
            ->values();

        return self::$capacityLabels[$companyId] = match ($limits->count()) {
            0 => '3',
            1 => (string) $limits->first(),
            default => 'varies',
        };
    }
}
