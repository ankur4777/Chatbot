<?php

namespace App\Filament\Agent\Pages;

use App\Filament\Agent\Concerns\HasAgentAccess;
use App\Models\ChatConversation;
use App\Support\BrowserTime;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MyActiveChats extends Page implements HasTable
{
    use HasAgentAccess;
    use InteractsWithTable;

    protected static ?string $title = 'My Active Chats';

    protected static ?string $navigationLabel = 'My Active Chats';

    protected static ?int $navigationSort = 20;

    protected static string|\BackedEnum|null $navigationIcon =
        Heroicon::OutlinedChatBubbleLeftRight;

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

                TextColumn::make('live_started_at')
                    ->label('Started')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->live_started_at
                                ? BrowserTime::format(
                                    $record->live_started_at,
                                    'd M Y, h:i A'
                                )
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('updated_at')
                    ->label('Last Activity')
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

                TextColumn::make('status')
                    ->badge()
                    ->color('success'),
            ])
            ->recordUrl(
                fn ($record) =>
                    ConversationView::getUrl(
                        ['conversation' => $record->id],
                        panel: 'agent'
                    )
            )
            ->recordActions([
                Action::make('open')
                    ->label('Open')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(
                        fn ($record) =>
                            ConversationView::getUrl(
                                ['conversation' => $record->id],
                                panel: 'agent'
                            )
                    ),
            ])
            ->defaultSort('updated_at', 'desc');
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
            ->where('status', 'live_active')
            ->where('assigned_agent_id', auth()->id());
    }
}
