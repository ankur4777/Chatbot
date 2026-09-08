<?php

namespace App\Filament\Client\Pages;

use App\Filament\Client\Concerns\HasSelectedLiveChatWebsite;
use App\Filament\Client\Concerns\RequiresLiveChatAccess;
use App\Models\ChatConversation;
use App\Support\BrowserTime;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaitingChats extends Page implements HasTable
{
    use InteractsWithTable;
    use HasSelectedLiveChatWebsite;
    use RequiresLiveChatAccess;

    protected static ?string $title = 'Waiting Chats';

    protected static ?string $navigationLabel = 'Waiting Chats';

    protected static string|\UnitEnum|null $navigationGroup = 'Live Chat';

    protected static ?int $navigationSort = 20;

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClock;

    protected string $view = 'filament.client.pages.table-page';

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
                    ->label('Waiting Since')
                    ->since()
                    ->tooltip(
                        fn ($record) =>
                            $record->handoff_requested_at
                                ? BrowserTime::format($record->handoff_requested_at, 'd M Y, h:i A')
                                : 'N/A'
                    )
                    ->sortable(),

                TextColumn::make('last_message')
                    ->label('Last Message')
                    ->state(
                        fn ($record) =>
                            $record->messages()->latest('id')->value('message')
                    )
                    ->limit(60)
                    ->wrap(),
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
                    $this->clientCompanyId()
                )
            )
            ->where('website_id', $this->selectedLiveChatWebsiteId())
            ->where('status', 'waiting_agent');
    }
}
