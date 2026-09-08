<?php

namespace App\Events;

use App\Models\ChatConversation;
use App\Models\LiveChatSession;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LiveChatClosed implements ShouldBroadcastNow
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
                'live-chat.' . $this->conversation->id
            ),
            new PrivateChannel(
                'company-live-chat.' . $this->conversation->website?->company_id
            ),
        ];
    }

    public function broadcastAs(): string
    {
        return 'LiveChatClosed';
    }

    public function broadcastWith(): array
    {
        $conversationEnded =
            in_array($this->conversation->status, ['closed', 'resolved', 'ended'], true);
        $pendingRating = LiveChatSession::query()
            ->with('agent')
            ->where('conversation_id', $this->conversation->id)
            ->whereNotNull('agent_id')
            ->whereNotNull('ended_at')
            ->where('rating_status', 'pending')
            ->latest('ended_at')
            ->latest('id')
            ->first();

        return [
            'conversation_id' => $this->conversation->id,
            'status' => $this->conversation->status,
            'mode' => $this->conversation->mode,
            'assigned_agent_id' => $this->conversation->assigned_agent_id,
            'closed_by' => $this->conversation->closed_by_id,
            'live_chat_ended' => ! $conversationEnded,
            'conversation_ended' => $conversationEnded,
            'message' => $conversationEnded
                ? 'Conversation closed.'
                : 'Live chat ended. You can continue chatting or connect to support team again.',
            'pending_rating' => $pendingRating
                ? [
                    'session_id' => $pendingRating->id,
                    'agent_name' => $pendingRating->agent?->name,
                    'ended_at' => optional($pendingRating->ended_at)->toISOString(),
                ]
                : null,
        ];
    }
}
