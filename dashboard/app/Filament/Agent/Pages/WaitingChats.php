<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Concerns\HasAgentAccess;
use App\Models\ChatConversation;
use App\Services\LiveChatService;
use App\Support\BrowserTime;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class WaitingChats extends Page implements HasTable
{
    use HasAgentAccess;
    use InteractsWithTable;

    protected static ?string $title = 'Waiting Chats';

    protected static ?string $navigationLabel = 'Waiting Chats';

    protected static ?int $navigationSort = 10;

    protected static string|\BackedEnum|null $navigationIcon =
        Heroicon::OutlinedInbox;

    protected string $view = 'filament.agent.pages.table-page';

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                TextColumn::make('visitor.visitor_uuid')
                    ->label('Visitor')
                    ->formatStateUsing(
                        fn ($state) =>
                            $state ? 'Visitor ' . substr($state, 0, 8) : 'Unknown'
                    ),

                TextColumn::make('website.name')
                    ->label('Website')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('handoff_requested_at')
                    ->label('Requested')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->handoff_requested_at
                                ? BrowserTime::format(
                                    $record->handoff_requested_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('last_message')
                    ->label('Last Message')
                    ->state(
                        fn ($record) =>
                            $record->messages()
                                ->latest('id')
                                ->value('message')
                    )
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color('warning'),
            ])
            ->recordActions([
                Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function (ChatConversation $record): void {
                        try {
                            $conversation = app(LiveChatService::class)
                                ->acceptConversation($record, auth()->user());

                            Notification::make()
                                ->title('Conversation accepted.')
                                ->success()
                                ->send();

                            $this->redirect(
                                ConversationView::getUrl(
                                    ['conversation' => $conversation->id],
                                    panel: 'agent'
                                )
                            );
                        } catch (
                            AuthorizationException |
                            InvalidArgumentException $exception
                        ) {
                            Notification::make()
                                ->title('Unable to accept conversation.')
                                ->body($exception->getMessage())
                                ->warning()
                                ->send();
                        }
                    }),
            ])
            ->defaultSort('handoff_requested_at', 'asc');
    }

    protected function getTableQuery(): Builder
    {
        return ChatConversation::query()
            ->with(['visitor', 'website'])
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $this->agentCompanyId()
                )
            )
            ->where('status', 'waiting_agent');
    }
}
