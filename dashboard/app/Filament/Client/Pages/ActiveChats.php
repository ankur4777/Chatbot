<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatAgent;
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

class ActiveChats extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatAgent;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Active Chats';

    protected static ?string $navigationLabel = 'Active Chats';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 30;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

    protected string $view = 'filament.client.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
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

                TextColumn::make('agent_chat_status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (ChatConversation $record): string => $this->agentChatStatusLabel($record))
                    ->color(fn (ChatConversation $record): string => $this->agentChatStatusColor($record)),

                TextColumn::make('live_started_at')
                    ->label('Started At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->live_started_at
                                ? BrowserTime::format($record->live_started_at, 'd M Y, h:i A')
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('last_message')
                    ->label('Last Message')
                    ->state(
                        fn ($record) =>
                            $record->latest_message_text
                    )
                    ->placeholder('No messages')
                    ->limit(60)
                    ->wrap(),

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
            ])
            ->defaultSort('latest_activity_message_created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $selectedAgentId = $this->selectedLiveChatAgentId();

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
            ->where('status', 'live_active')
            ->where('updated_at', '>=', $this->recentChatCutoff())
            ->when(
                $selectedAgentId,
                fn ($query) => $query->where(
                    'assigned_agent_id',
                    $selectedAgentId
                )
            );
    }

    protected function recentChatCutoff()
    {
        return now()->subDays(30);
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
            ?? $conversation->live_started_at
            ?? $conversation->assigned_at;
    }
}
