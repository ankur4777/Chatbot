<?php

namespace App\Events;

use App\Models\ChatMessage;
use App\Services\ChatService;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessageSent implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatMessage $chatMessage
    ) {
        $this->chatMessage->loadMissing('conversation');
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            'live-chat.' . $this->chatMessage->conversation_id
        );
    }

    public function broadcastAs(): string
    {
        return 'LiveChatMessageSent';
    }

    public function broadcastWith(): array
    {
        $attachmentUrl = $this->chatMessage->attachment
            ? route('agent.chats.attachments.show', [
                'conversation' => $this->chatMessage->conversation_id,
                'message' => $this->chatMessage->id,
            ])
            : null;

        return [
            'conversation_id' => $this->chatMessage->conversation_id,
            'message_id' => $this->chatMessage->id,
            'sender_type' => $this->chatMessage->sender_type,
            'sender_id' => $this->chatMessage->sender_id,
            'message' => $this->chatMessage->message,
            'attachment' => app(ChatService::class)->attachmentPayload(
                $this->chatMessage,
                $attachmentUrl
            ),
            'created_at' => optional($this->chatMessage->created_at)->toISOString(),
        ];
    }
}
