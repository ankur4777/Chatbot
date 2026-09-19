<?php

namespace App\Services;

use App\Events\AgentNotificationCreated;
use App\Models\AgentNotification;
use App\Models\ChatbotLead;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LiveChatSession;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AgentNotificationService
{
    public function create(array $attributes, bool $broadcast = true): AgentNotification
    {
        $notification = AgentNotification::create($attributes);

        if ($broadcast) {
            try {
                broadcast(new AgentNotificationCreated($notification));
            } catch (\Throwable $exception) {
                Log::warning('Agent notification broadcast failed.', [
                    'notification_id' => $notification->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return $notification;
    }

    public function visitorMessage(ChatMessage $message): ?AgentNotification
    {
        if (! in_array($message->sender_type, ['visitor', 'user'], true)) {
            return null;
        }

        $conversation = $message->conversation?->loadMissing(['visitor', 'website']);

        if (! $conversation || ! $conversation->assigned_agent_id || $conversation->status !== 'live_active') {
            return null;
        }

        $agent = User::query()->whereKey($conversation->assigned_agent_id)->first();

        if (! $agent || $agent->role !== 'agent') {
            return null;
        }

        $visitor = $this->visitorLabel($conversation);

        return $this->create([
            'agent_id' => $agent->id,
            'company_id' => $agent->company_id,
            'website_id' => $conversation->website_id,
            'conversation_id' => $conversation->id,
            'type' => AgentNotification::TYPE_NEW_VISITOR_MESSAGE,
            'title' => 'New message from ' . $visitor,
            'message' => $message->message ?: 'Visitor sent an attachment.',
            'data' => [
                'action_label' => 'Open Chat',
                'action_url' => route('agent.chats.show', $conversation, false),
                'visitor' => $visitor,
            ],
        ]);
    }

    public function missedChatAssigned(ChatbotLead $lead): ?AgentNotification
    {
        if (! $lead->assigned_agent_id) {
            return null;
        }

        $agent = User::query()->whereKey($lead->assigned_agent_id)->first();

        if (! $agent || $agent->role !== 'agent') {
            return null;
        }

        return $this->create([
            'agent_id' => $agent->id,
            'company_id' => $agent->company_id,
            'website_id' => $lead->website_id,
            'missed_chat_id' => $lead->id,
            'type' => AgentNotification::TYPE_MISSED_CHAT_ASSIGNED,
            'title' => 'A missed chat has been assigned to you.',
            'message' => $lead->message ?: 'Open this missed chat and follow up with the visitor.',
            'data' => [
                'action_label' => 'Open',
                'action_url' => route('agent.missed-chats.show', $lead, false),
            ],
        ]);
    }

    public function followUpReminder(ChatbotLead $lead): ?AgentNotification
    {
        if (! $lead->assigned_agent_id) {
            return null;
        }

        $agent = User::query()->whereKey($lead->assigned_agent_id)->first();

        if (! $agent || $agent->role !== 'agent') {
            return null;
        }

        $lead->loadMissing(['visitor', 'website']);
        $visitor = $this->leadVisitorLabel($lead);
        $website = $lead->website?->name ?? 'Unknown website';

        return $this->create([
            'agent_id' => $agent->id,
            'company_id' => $agent->company_id,
            'website_id' => $lead->website_id,
            'missed_chat_id' => $lead->id,
            'type' => AgentNotification::TYPE_FOLLOW_UP_REMINDER,
            'title' => 'Follow-up Reminder',
            'message' => "Follow up with {$visitor} for {$website}.",
            'data' => [
                'action_label' => 'Open Follow-up',
                'action_url' => route('agent.missed-chats.show', $lead, false),
                'due_at' => optional($lead->next_followup_at)->toISOString(),
            ],
        ]);
    }

    public function conversationClosed(LiveChatSession $session): ?AgentNotification
    {
        $session->loadMissing('conversation.visitor', 'conversation.website', 'agent');

        if (! $session->agent_id || ! $session->agent) {
            return null;
        }

        return $this->create([
            'agent_id' => $session->agent_id,
            'company_id' => $session->agent->company_id,
            'website_id' => $session->conversation?->website_id,
            'conversation_id' => $session->conversation_id,
            'type' => AgentNotification::TYPE_CONVERSATION_CLOSED,
            'title' => 'Conversation closed successfully.',
            'message' => 'Conversation #' . $session->conversation_id . ' was closed.',
            'data' => [
                'action_label' => 'View',
                'action_url' => route('agent.closed.show', $session, false),
            ],
        ]);
    }

    protected function visitorLabel(ChatConversation $conversation): string
    {
        return $conversation->visitor?->displayName() ?? 'Visitor';
    }

    protected function leadVisitorLabel(ChatbotLead $lead): string
    {
        return $lead->visitor?->displayName() ?? ($lead->name ?: 'Visitor');
    }
}
