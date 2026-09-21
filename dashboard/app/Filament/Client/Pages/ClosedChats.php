<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatAgent;
use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\LiveChatSession;
use App\Support\BrowserTime;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ClosedChats extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatAgent;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Closed Chats (Last 30 Days)';

    protected static ?string $navigationLabel = 'Closed Chats';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 40;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedArchiveBox;

    protected string $view = 'filament.client.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->headerActions([
                Action::make('downloadAll')
                    ->label('Download All PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(fn () => route('client.closed-chats.download-all', [
                        'website' => $this->selectedLiveChatWebsiteId(),
                        'agent' => $this->selectedLiveChatAgentId(),
                    ])),
            ])
            ->columns([
                TextColumn::make('conversation.visitor.name')
                    ->label('Visitor')
                    ->state(fn (LiveChatSession $record) => $record->conversation?->visitor?->displayName() ?? 'Unknown'),

                TextColumn::make('conversation.visitor.email')
                    ->label('Email')
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('conversation.visitor.phone')
                    ->label('Phone')
                    ->placeholder('-')
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->placeholder('Unassigned'),

                TextColumn::make('ended_by')
                    ->label('Closed By')
                    ->formatStateUsing(
                        fn ($state) => $state === 'visitor'
                            ? 'Visitor'
                            : 'Agent'
                    )
                    ->badge()
                    ->color('gray'),

                TextColumn::make('ended_at')
                    ->label('Closed At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->ended_at
                                ? BrowserTime::format($record->ended_at, 'd M Y, h:i A')
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('duration')
                    ->label('Duration')
                    ->state(fn ($record) => $this->formatDuration($record))
                    ->placeholder('N/A'),

                TextColumn::make('rating')
                    ->label('Rating')
                    ->state(fn ($record) => $this->formatRating($record))
                    ->badge()
                    ->color(fn (LiveChatSession $record) => $this->ratingColor($record))
                    ->placeholder('Not Rated'),

                TextColumn::make('feedback')
                    ->label('Feedback')
                    ->placeholder('No feedback')
                    ->limit(68)
                    ->wrap()
                    ->tooltip(
                        fn ($record) => filled($record->feedback)
                            ? \Illuminate\Support\Str::limit($record->feedback)
                            : null
                    ),

                TextColumn::make('note')
                    ->label('Agent Note')
                    ->placeholder('No note added')
                    ->wrap()
                    ->limit(160),
            ])
            ->recordActions([
                Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(
                        fn (LiveChatSession $record) =>
                            route('client.closed-chats.download', $record)
                    ),
            ])
            ->defaultSort('ended_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $selectedAgentId = $this->selectedLiveChatAgentId();

        return LiveChatSession::query()
            ->with(['conversation.visitor', 'conversation.website', 'agent'])
            ->whereNotNull('ended_at')
            ->where('ended_at', '>=', $this->recentChatCutoff())
            ->whereHas(
                'conversation.website',
                fn ($query) => $query
                    ->where('company_id', $this->clientCompanyId())
                    ->where('id', $this->selectedLiveChatWebsiteId())
            )
            ->when(
                $selectedAgentId,
                fn ($query) => $query->where('agent_id', $selectedAgentId)
            );
    }

    protected function recentChatCutoff()
    {
        return now()->subDays(30);
    }

    protected function formatDuration(LiveChatSession $session): ?string
    {
        if (! $session->started_at || ! $session->ended_at) {
            return null;
        }

        $seconds = $session->started_at->diffInSeconds(
            $session->ended_at,
            true
        );

        if ($seconds < 60) {
            return $seconds . ' sec';
        }

        $minutes = intdiv($seconds, 60);

        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0
            ? $hours . ' hr ' . $remainingMinutes . ' min'
            : $hours . ' hr';
    }

    protected function formatRating(LiveChatSession $session): ?string
    {
        if ($session->rating_status !== 'submitted' || ! $session->rating) {
            return null;
        }

        return str_repeat('★', $session->rating)
            . ' ' . $session->rating . '/5';
    }

    protected function ratingColor(LiveChatSession $session): string
    {
        if ($session->rating_status !== 'submitted' || ! $session->rating) {
            return 'gray';
        }

        return match (true) {
            $session->rating <= 2 => 'danger',
            $session->rating === 3 => 'warning',
            default => 'success',
        };
    }
}
