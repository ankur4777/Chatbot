<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LiveChatSession;
use App\Support\BrowserTime;
use BackedEnum;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LiveInbox extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Live Inbox';

    protected static ?string $navigationLabel = 'Live Inbox';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 10;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedInbox;

    protected string $view = 'filament.client.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns($this->columns())
            ->defaultSort('latest_activity_message_created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        return ChatConversation::query()
            ->select('chat_conversations.*')
            ->with(['visitor', 'website', 'assignedAgent', 'activeLiveChatSession'])
            ->addSelect([
                'latest_message_text' => ChatMessage::query()
                    ->select('message')
                    ->whereColumn(
                        'chat_messages.conversation_id',
                        'chat_conversations.id'
                    )
                    ->orderByDesc('created_at')
                    ->orderByDesc('id')
                    ->limit(1),
                'latest_activity_message_created_at' => ChatMessage::query()
                    ->selectRaw('MAX(chat_messages.created_at)')
                    ->whereColumn(
                        'chat_messages.conversation_id',
                        'chat_conversations.id'
                    )
                    ->whereRaw(
                        'chat_messages.created_at >= COALESCE(chat_conversations.live_started_at, chat_conversations.assigned_at, chat_conversations.handoff_requested_at, chat_conversations.started_at, chat_conversations.created_at)'
                    ),
            ])
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->clientCompanyId()
                )
            )
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->where('updated_at', '>=', $this->recentChatCutoff())
            ->whereIn('status', ['waiting_agent', 'live_active']);
    }

    protected function recentChatCutoff()
    {
        return now()->subDays(30);
    }

    protected function columns(): array
    {
        return [
            TextColumn::make('visitor.name')
                ->label('Visitor')
                ->state(fn (ChatConversation $record) => $record->visitor?->displayName() ?? 'Unknown'),

            TextColumn::make('visitor.email')
                ->label('Email')
                ->placeholder('-')
                ->copyable()
                ->toggleable(),

            TextColumn::make('visitor.phone')
                ->label('Phone')
                ->placeholder('-')
                ->copyable()
                ->toggleable(),

            TextColumn::make('assignedAgent.name')
                ->label('Agent')
                ->placeholder('Unassigned'),

            TextColumn::make('status')
                ->badge()
                ->formatStateUsing(
                    fn (string $state, ChatConversation $record): string => match ($state) {
                        'waiting_agent' => 'Waiting',
                        'live_active' => $this->agentChatStatusLabel($record),
                        default => ucfirst(str_replace('_', ' ', $state)),
                    }
                )
                ->color(
                    fn (string $state, ChatConversation $record): string => match ($state) {
                        'waiting_agent' => 'warning',
                        'live_active' => $this->agentChatStatusColor($record),
                        default => 'gray',
                    }
                ),

            TextColumn::make('last_message')
                ->label('Last Message')
                ->state(
                    fn ($record) =>
                        $record->latest_message_text
                )
                ->placeholder('No messages')
                ->limit(60)
                ->wrap(),

            TextColumn::make('live_started_at')
                ->label('Started At')
                ->state(fn ($record) => $this->startedAt($record))
                ->since()
                ->tooltip(
                    fn ($record) => BrowserTime::format(
                        $this->startedAt($record),
                        'd M Y, h:i A'
                    )
                )
                ->sortable(),

            TextColumn::make('latest_activity_message_created_at')
                ->label('Last Activity')
                ->state(fn ($record) => $this->lastActivityAt($record))
                ->since()
                ->tooltip(
                    fn ($record) => BrowserTime::format(
                        $this->lastActivityAt($record),
                        'd M Y, h:i A'
                    )
                )
                ->sortable(),
        ];
    }

    protected function startedAt(ChatConversation $conversation): ?CarbonInterface
    {
        return $conversation->live_started_at
            ?? $conversation->assigned_at
            ?? $conversation->handoff_requested_at;
    }

    protected function agentChatStatusLabel(ChatConversation $conversation): string
    {
        $status = $conversation->activeLiveChatSession?->agent_chat_status
            ?: LiveChatSession::AGENT_CHAT_STATUS_ACTIVE;

        return LiveChatSession::agentChatStatusLabels()[$status] ?? 'Active';
    }

    protected function agentChatStatusColor(ChatConversation $conversation): string
    {
        return match (
            $conversation->activeLiveChatSession?->agent_chat_status
                ?: LiveChatSession::AGENT_CHAT_STATUS_ACTIVE
        ) {
            LiveChatSession::AGENT_CHAT_STATUS_ON_HOLD => 'warning',
            LiveChatSession::AGENT_CHAT_STATUS_AWAITING_VISITOR => 'info',
            default => 'success',
        };
    }

    protected function lastActivityAt(ChatConversation $conversation): ?CarbonInterface
    {
        if ($conversation->latest_activity_message_created_at) {
            return Carbon::parse($conversation->latest_activity_message_created_at);
        }

        return $conversation->updated_at
            ?? $this->startedAt($conversation);
    }
}
