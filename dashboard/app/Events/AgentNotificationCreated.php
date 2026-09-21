<?php

namespace App\Events;

use App\Models\AgentNotification;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgentNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AgentNotification $notification)
    {
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('agent-notifications.' . $this->notification->agent_id);
    }

    public function broadcastAs(): string
    {
        return 'AgentNotificationCreated';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'conversation_id' => $this->notification->conversation_id,
            'missed_chat_id' => $this->notification->missed_chat_id,
            'action_url' => $this->notification->data['action_url'] ?? null,
            'created_at' => optional($this->notification->created_at)->toISOString(),
            'unread_count' => AgentNotification::query()
                ->where('agent_id', $this->notification->agent_id)
                ->unread()
                ->count(),
        ];
    }
}
