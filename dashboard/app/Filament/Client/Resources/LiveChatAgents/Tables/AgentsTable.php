<?php

namespace App\Filament\Client\Resources\LiveChatAgents\Tables;

use App\Models\User;
use App\Models\WebsiteSetting;
use App\Support\BrowserTime;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgentsTable
{
    protected static array $capacityLabels = [];

    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $companyId = auth()->user()?->company_id;

                return $query->withCount([
                    'assignedConversations as active_live_chats_count' => fn (Builder $query) =>
                        $query
                            ->where('status', 'live_active')
                            ->whereHas(
                                'website',
                                fn (Builder $query) =>
                                    $query->where('company_id', $companyId)
                            ),
                ]);
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
                                : $query
                    ),
            ])
            ->recordActions([
                EditAction::make(),
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
