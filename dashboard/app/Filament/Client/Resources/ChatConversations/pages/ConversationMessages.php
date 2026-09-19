<?php

namespace App\Filament\Client\Resources\ChatConversations\Pages;

use App\Filament\Client\Resources\ChatConversations\ChatConversationResource;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Support\BrowserTime;
use App\Models\Website;
use Filament\Actions\Action;
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
        return [
            Action::make('downloadConversation')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn (): string => route(
                    'client.chatbot-conversations.download-one',
                    $this->conversationId
                )),
        ];
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
    ->state(function (ChatMessage $record): string {
        if ($record->attachment_type === 'audio' && $record->attachment) {
            return self::voicePlayerHtml($record);
        }

        return e($record->message ?: 'Attachment');
    })
    ->html()
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
                    ->state(function (ChatMessage $record): string {
                        if ($record->attachment_type === 'audio' && $record->attachment) {
                            return self::voicePlayerHtml($record);
                        }

                        return e($record->message ?: 'Attachment');
                    })
                    ->html()
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

    protected static function voicePlayerHtml(ChatMessage $record): string
    {
        $url = e(route('client.chat-attachments.show', $record));
        $duration = (int) ($record->metadata['attachment']['duration'] ?? 0);
        $bars = collect(range(0, 17))
            ->map(fn (int $index): string => '<span style="height:' . (8 + (($index * 7) % 18)) . 'px;background:#d1fae5;border-radius:999px;flex:1;min-width:2px;"></span>')
            ->implode('');

        return '<div data-transcript-voice style="display:grid;grid-template-columns:30px minmax(110px,1fr) auto;align-items:center;gap:8px;min-width:200px;max-width:280px;width:260px;border:1px solid #334155;border-radius:999px;padding:7px 9px;background:#111827;">'
            . '<audio preload="metadata" src="' . $url . '" data-duration="' . $duration . '" style="display:none" onloadedmetadata="const p=this.closest(\'[data-transcript-voice]\');const t=p.querySelector(\'[data-voice-time]\');const d=this.duration||Number(this.dataset.duration)||0;t.textContent=Math.floor(d/60)+\':\'+String(Math.floor(d%60)).padStart(2,\'0\');" ontimeupdate="const p=this.closest(\'[data-transcript-voice]\');const d=this.duration||Number(this.dataset.duration)||0;const c=this.currentTime||0;p.querySelector(\'[data-voice-progress]\').style.width=(d?Math.min(c/d*100,100):0)+\'%\';p.querySelector(\'[data-voice-time]\').textContent=this.paused||c===0?Math.floor(d/60)+\':\'+String(Math.floor(d%60)).padStart(2,\'0\'):Math.floor(c/60)+\':\'+String(Math.floor(c%60)).padStart(2,\'0\')+\' / \'+Math.floor(d/60)+\':\'+String(Math.floor(d%60)).padStart(2,\'0\');"></audio>'
            . '<button type="button" aria-label="Play voice note" style="height:30px;width:30px;border:0;border-radius:999px;background:#22c55e;color:#fff;font-weight:800;cursor:pointer;" onclick="const a=this.parentElement.querySelector(\'audio\');if(a.paused){a.play();this.textContent=\'Ⅱ\';}else{a.pause();this.textContent=\'▶\';}a.onended=()=>{this.textContent=\'▶\';};">▶</button>'
            . '<button type="button" aria-label="Seek voice note" style="height:28px;border:0;border-radius:999px;background:#374151;position:relative;overflow:hidden;cursor:pointer;padding:0;" onclick="const a=this.parentElement.querySelector(\'audio\');const r=this.getBoundingClientRect();if(a.duration){a.currentTime=((event.clientX-r.left)/r.width)*a.duration;}"><span data-voice-progress style="position:absolute;inset:0 auto 0 0;width:0;background:#166534;"></span><span style="position:absolute;inset:0 8px;display:flex;align-items:center;gap:3px;">'
            . $bars
            . '</span></button>'
            . '<span data-voice-time style="color:#e5e7eb;font-size:11px;font-weight:700;white-space:nowrap;">'
            . ($duration > 0 ? floor($duration / 60) . ':' . str_pad((string) ($duration % 60), 2, '0', STR_PAD_LEFT) : '0:00')
            . '</span></div>';
    }
}
