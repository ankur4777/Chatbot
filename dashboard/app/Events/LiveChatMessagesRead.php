<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatMessagesRead implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatConversation $conversation,
        public array $messageIds
    ) {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel(
            'live-chat.' . $this->conversation->id
        );
    }

    public function broadcastAs(): string
    {
        return 'LiveChatMessagesRead';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'message_ids' => $this->messageIds,
        ];
    }
}
