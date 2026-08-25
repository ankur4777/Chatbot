<?php

namespace App\Filament\Client\Resources\ChatConversations\Pages;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\BrowserTime;
use App\Models\Website;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConversationMessages extends ListRecords
{
    protected static string $resource = ChatConversationResource::class;

    public int $websiteId;

    public int $conversationId;

    public function mount(): void
    {
        $this->websiteId =
            (int) request()->route('website');

        $this->conversationId =
            (int) request()->route('conversation');

        // Security checks
        $this->getWebsite();
        $this->getConversation();

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

    protected function getConversation(): ChatConversation
    {
        return ChatConversation::query()
            ->whereKey($this->conversationId)
            ->where(
                'website_id',
                $this->websiteId
            )
            ->firstOrFail();
    }

    public function getTitle(): string
    {
        return 'Conversation #' . $this->conversationId;
    }

    protected function getTableQuery(): Builder
    {
        return ChatMessage::query()
            ->where(
                'conversation_id',
                $this->conversationId
            );
    }

   public function getSubheading(): ?string
{
    $conversation = $this->getConversation();

    $visitorUuid = $conversation->visitor?->visitor_uuid;

    $visitor = $visitorUuid
        ? 'Visitor ' . substr($visitorUuid, 0, 8)
        : 'Unknown Visitor';

    $messageCount = $conversation->messages()->count();

    $status = ucfirst($conversation->status ?? 'Unknown');

    $date = $conversation->started_at
        ? $conversation->started_at->format('d M Y')
        : 'Date N/A';

    return "{$visitor} • {$messageCount} Messages • {$status} • {$date}";
}
    protected function getHeaderActions(): array
    {
        return [];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())

            ->columns([
                TextColumn::make('sender_type')
    ->label('Sender')
    ->badge()
    ->formatStateUsing(
        fn ($state) => match ($state) {
            'visitor' => 'Visitor',
            'user' => 'Visitor',
            'bot' => 'Bot',
            'assistant' => 'Bot',
            'agent' => 'Agent',
            default => ucfirst($state ?? 'Unknown'),
        }
    )
    ->color(
        fn ($state): string => match ($state) {
            'visitor', 'user' => 'info',
            'bot', 'assistant' => 'success',
            'agent' => 'warning',
            default => 'gray',
        }
    ),

TextColumn::make('message')
    ->label('Message')
    ->wrap()
    ->tooltip(fn ($record) => $record->message),

TextColumn::make('created_at')
    ->label('Time')
    ->dateTime('h:i A')
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

                TextColumn::make('sender_type')
                    ->label('Sender')
                    ->badge()
                    ->formatStateUsing(
                        fn ($state) => match ($state) {
                            'visitor' => 'Visitor',
                            'user' => 'Visitor',
                            'bot' => 'Bot',
                            'assistant' => 'Bot',
                            'agent' => 'Agent',
                            default => ucfirst($state ?? 'Unknown'),
                        }
                    )
                    ->color(
                        fn ($state): string => match ($state) {
                            'visitor', 'user' => 'info',
                            'bot', 'assistant' => 'success',
                            'agent' => 'warning',
                            default => 'gray',
                        }
                    ),

                TextColumn::make('message')
                    ->label('Message')
                    ->wrap()
                    ->tooltip(
                        fn ($record) => $record->message
                    ),

                TextColumn::make('created_at')
    ->label('Time')
    ->dateTime('h:i A')
    ->tooltip(
        fn ($record) =>
            $record->created_at
                ? $record->created_at->format('d M Y, h:i A')
                : 'N/A'
    )
    ->sortable(),
            ])

            ->recordUrl(null)

            ->recordActions([])

            ->defaultSort(
                'created_at',
                'asc'
            );
    }
}