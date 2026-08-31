<?php

namespace App\Filament\Client\Resources\ChatConversations\Pages;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\Website;
use Filament\Actions\Action;
use App\Support\BrowserTime;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WebsiteConversations extends ListRecords
{
    protected static string $resource = ChatConversationResource::class;

    public int $websiteId;

    public function mount(): void
    {
        $this->websiteId = (int) request()->route('website');

        $this->getWebsite();

        $this->endInactiveConversations();

        parent::mount();
    }

    protected function getWebsite(): Website
    {
        return Website::query()
            ->whereKey($this->websiteId)
            ->where(
                'company_id',
                auth()->user()->company_id
            )
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return $this->getWebsite()->name . ' Conversations';
    }

    protected function getTableQuery(): Builder
    {
        return ChatConversation::query()
            ->where('website_id', $this->websiteId)
            ->with('visitor')
            ->withCount('messages');
    }

    private function endInactiveConversations(): void
    {
        $cutoff = now()->subMinutes(5);

        ChatConversation::query()
            ->where('website_id', $this->websiteId)
            ->where('status', 'active')
            ->where('created_at', '<=', $cutoff)
            ->whereDoesntHave(
                'messages',
                fn ($query) => $query->where('created_at', '>', $cutoff),
            )
            ->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([

                TextColumn::make('id')
                    ->label('Conversation ID')
                    ->formatStateUsing(
                        fn ($state) => 'Conversation #' . $state
                    )
                    ->sortable(),

                TextColumn::make('visitor.visitor_uuid')
                    ->label('Visitor')
                    ->formatStateUsing(
                        fn ($state) =>
                            $state
                                ? 'Visitor ' . substr($state, 0, 8)
                                : '—'
                    )
                    ->tooltip(
                        fn ($record) =>
                            $record->visitor?->visitor_uuid
                                ?: 'No Visitor ID'
                    )
                    ->copyable()
                    ->copyableState(
                        fn ($record) =>
                            $record->visitor?->visitor_uuid
                    ),

                TextColumn::make('messages_count')
                    ->label('Messages')
                    ->sortable(),

                TextColumn::make('status')
    ->label('Status')
    ->badge()
    ->formatStateUsing(
        fn ($state) => ucfirst($state)
    )
    ->color(
        fn ($state): string => match ($state) {
            'active' => 'success',
            'ended' => 'gray',
            'pending' => 'warning',
            default => 'gray',
        }
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
            ])

            ->recordUrl(
                fn ($record) =>
                    ChatConversationResource::getUrl(
                        'conversation-messages',
                        [
                            'website' => $this->websiteId,
                            'conversation' => $record->id,
                        ]
                    )
            )

            ->recordActions([
                Action::make('viewMessages')
    ->label('View Chat')
    ->icon('heroicon-o-chat-bubble-left-right')
    ->color('gray')
    ->url(
        fn ($record) =>
            ChatConversationResource::getUrl(
                'conversation-messages',
                [
                    'website' => $this->websiteId,
                    'conversation' => $record->id,
                ]
            )
    ),
            ])

            ->defaultSort('updated_at', 'desc');
    }
}
