<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatRequested implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatConversation $conversation
    ) {
        $this->conversation->loadMissing('website');
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'company-live-chat.' . $this->conversation->website?->company_id
            ),
            new PrivateChannel(
                'live-chat.' . $this->conversation->id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LiveChatRequested';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'status' => $this->conversation->status,
            'mode' => $this->conversation->mode,
            'website_id' => $this->conversation->website_id,
        ];
    }
}
