<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\ChatbotLead;
use App\Models\User;
use App\Services\AgentNotificationService;
use App\Support\BrowserTime;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OfflineRequests extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Missed Chats (Last 30 Days)';

    protected static ?string $navigationLabel = 'Missed Chats';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 65;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedDocumentText;

    protected string $view = 'filament.client.pages.table-page';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadMissedChats')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->url(fn (): string => route('client.missed-chats.download', [
                    'website' => $this->selectedLiveChatWebsiteId(),
                ]))
                ->openUrlInNewTab(),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('name')
                    ->label('Visitor Name')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Unknown'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->searchable()
                    ->copyable()
                    ->placeholder('N/A'),

                TextColumn::make('notes')
                    ->label('Message')
                    ->limit(300)
                    ->wrap()
                    ->placeholder('No message'),

                TextColumn::make('assignedAgent.name')
                    ->label('Assigned Agent')
                    ->placeholder('Unassigned')
                    ->sortable(),

                TextColumn::make('followup_status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(
                        fn (?string $state): string =>
                            ChatbotLead::followupStatusLabels()[$state ?? 'pending']
                            ?? 'Pending'
                    )
                    ->color(
                        fn (?string $state): string => match ($state) {
                            'pending' => 'gray',
                            'assigned' => 'info',
                            'contacted' => 'warning',
                            'follow_up_required' => 'warning',
                            'resolved' => 'success',
                            'unable_to_reach' => 'danger',
                            default => 'gray',
                        }
                    )
                    ->sortable(),

                TextColumn::make('assigned_at')
                    ->label('Assigned At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->assigned_at
                                ? BrowserTime::format(
                                    $record->assigned_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->placeholder('Not assigned')
                    ->sortable(),

                TextColumn::make('agent_note')
                    ->label('Agent Note')
                    ->limit(180)
                    ->wrap()
                    ->placeholder('No note'),

                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->created_at
                                ? BrowserTime::format(
                                    $record->created_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->updated_at
                                ? BrowserTime::format(
                                    $record->updated_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->sortable(),
            ])
            ->recordUrl(null)
            ->recordActions([
                Action::make('assignAgent')
                    ->label(fn (ChatbotLead $record): string =>
                        $record->assigned_agent_id ? 'Change Agent' : 'Assign Agent'
                    )
                    ->icon('heroicon-o-user-plus')
                    ->color('warning')
                    ->form([
                        Select::make('assigned_agent_id')
                            ->label('Agent')
                            ->options(fn (ChatbotLead $record): array => User::query()
                                ->where('role', 'agent')
                                ->where('company_id', $this->clientCompanyId())
                                ->where('status', true)
                                ->whereHas(
                                    'assignedWebsites',
                                    fn (Builder $query) => $query
                                        ->whereKey($record->website_id)
                                        ->where(
                                            'websites.company_id',
                                            $this->clientCompanyId()
                                        )
                                )
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->fillForm(fn (ChatbotLead $record): array => [
                        'assigned_agent_id' => $record->assigned_agent_id,
                    ])
                    ->action(function (ChatbotLead $record, array $data): void {
                        $this->assignMissedChat($record, (int) $data['assigned_agent_id']);
                    }),

                Action::make('viewDetails')
                    ->label('View Details')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->modalHeading('Missed Chat Details')
                    ->modalContent(
                        fn (ChatbotLead $record) => view(
                            'filament.client.pages.missed-chat-details',
                            ['lead' => $record->load(['website', 'assignedAgent'])]
                        )
                    )
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
            ])
            ->defaultSort('created_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        return ChatbotLead::query()
            ->with(['website', 'conversation', 'assignedAgent'])
            ->where('source', 'live_chat_offline_request')
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->where('created_at', '>=', $this->recentChatCutoff())
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->clientCompanyId()
                )
            );
    }

    protected function recentChatCutoff()
    {
        return now()->subDays(30);
    }

    protected function assignMissedChat(
        ChatbotLead $lead,
        int $agentId
    ): void {
        abort_unless($this->clientOwnsMissedChat($lead), 404);

        $agent = User::query()
            ->whereKey($agentId)
            ->where('role', 'agent')
            ->where('company_id', $this->clientCompanyId())
            ->where('status', true)
            ->whereHas(
                'assignedWebsites',
                fn (Builder $query) => $query
                    ->whereKey($lead->website_id)
                    ->where('websites.company_id', $this->clientCompanyId())
            )
            ->firstOrFail();

        $previousAgentId = $lead->assigned_agent_id;

        $lead->forceFill([
            'assigned_agent_id' => $agent->id,
            'assigned_by' => auth()->id(),
            'assigned_at' => now(),
            'followup_status' => ($lead->followup_status ?? 'pending') === 'pending'
                ? 'assigned'
                : $lead->followup_status,
        ])->save();

        if ((int) $previousAgentId !== (int) $agent->id) {
            app(AgentNotificationService::class)->missedChatAssigned($lead->refresh());
        }
    }

    protected function clientOwnsMissedChat(ChatbotLead $lead): bool
    {
        return $lead->source === 'live_chat_offline_request'
            && $lead->website()
                ->where('company_id', $this->clientCompanyId())
                ->exists();
    }
}
