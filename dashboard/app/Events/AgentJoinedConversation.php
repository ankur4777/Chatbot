<?php

namespace App\Events;

use App\Models\ChatConversation;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentJoinedConversation implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public ChatConversation $conversation
    ) {
        $this->conversation->loadMissing(['assignedAgent', 'website']);
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(
                'live-chat.' . $this->conversation->id
            ),
            new PrivateChannel(
                'company-live-chat.' . $this->conversation->website?->company_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AgentJoinedConversation';
    }

    public function broadcastWith(): array
    {
        return [
            'conversation_id' => $this->conversation->id,
            'agent_id' => $this->conversation->assigned_agent_id,
            'agent_name' => $this->conversation->assignedAgent?->name,
            'status' => $this->conversation->status,
            'mode' => $this->conversation->mode,
        ];
    }
}
