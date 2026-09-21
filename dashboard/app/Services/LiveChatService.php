<?php

namespace App\Services;

use App\Events\AgentJoinedConversation;
use App\Events\LiveChatClosed;
use App\Events\LiveChatMessageSent;
use App\Events\LiveChatRequested;
use App\Models\ChatConversation;
use App\Models\LiveChatClosure;
use App\Models\LiveChatRating;
use App\Models\LiveChatSession;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class LiveChatService
{
    public function __construct(
        protected WebsiteFeatureService $websiteFeatureService,
        protected LiveChatAvailabilityService $availabilityService
    ) {
    }

    public function requestLiveChat(ChatConversation $conversation): ChatConversation
    {
        if (! $this->websiteFeatureService->isLiveChatEnabledForWebsite(
            $conversation->website
        )) {
            throw new InvalidArgumentException(
                'Live Chat is disabled for this website.'
            );
        }

        if ($conversation->status === 'waiting_agent') {
            return $conversation;
        }

        if ($conversation->status === 'live_active') {
            return $conversation;
        }

        if ($conversation->status !== 'active') {
            throw new InvalidArgumentException(
                'This conversation cannot be handed off to an agent.'
            );
        }

        $conversation->update([
            'status' => 'waiting_agent',
            'mode' => 'ai',
            'handoff_requested_at' => $conversation->handoff_requested_at
                ?? now(),
        ]);

        $conversation = $conversation->refresh();

        app(AgentNotificationService::class)->waitingChat($conversation);
        $this->broadcastSafely(new LiveChatRequested($conversation));

        return $conversation;
    }

    public function acceptConversation(
        ChatConversation $conversation,
        User $agent
    ): ChatConversation {
        $acceptedConversation = DB::transaction(function () use ($conversation, $agent) {
            $lockedConversation = ChatConversation::query()
                ->with('website.settings')
                ->whereKey($conversation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeAgentForConversation(
                $agent,
                $lockedConversation
            );

            if ($agent->availability_status !== 'online') {
                throw new AuthorizationException(
                    'Set yourself online before accepting live chats.'
                );
            }

            if ($lockedConversation->status !== 'waiting_agent') {
                throw new InvalidArgumentException(
                    'Only waiting conversations can be accepted.'
                );
            }

            $activeChats = $this->availabilityService
                ->countActiveChatsForAgent($agent);
            $maxActiveChats = $this->availabilityService
                ->maxActiveChatsForWebsite($lockedConversation->website);

            if ($activeChats >= $maxActiveChats) {
                throw new InvalidArgumentException(
                    'You have reached your active chat limit.'
                );
            }

            $updated = ChatConversation::query()
                ->whereKey($lockedConversation->getKey())
                ->where('status', 'waiting_agent')
                ->whereNull('assigned_agent_id')
                ->update([
                    'assigned_agent_id' => $agent->id,
                    'assigned_at' => now(),
                    'live_started_at' => now(),
                    'status' => 'live_active',
                    'mode' => 'live',
                ]);

            if ($updated !== 1) {
                throw new InvalidArgumentException(
                    'This conversation has already been accepted.'
                );
            }

            LiveChatSession::create([
                'conversation_id' => $lockedConversation->id,
                'agent_id' => $agent->id,
                'started_at' => now(),
                'rating_status' => 'pending',
                'agent_chat_status' => LiveChatSession::AGENT_CHAT_STATUS_ACTIVE,
            ]);

            $lockedConversation = $lockedConversation->refresh();

            return $lockedConversation;
        });

        $this->broadcastSafely(
            new AgentJoinedConversation($acceptedConversation)
        );

        return $acceptedConversation;
    }

    public function closeLiveConversation(
        ChatConversation $conversation,
        ?User $closedBy = null
    ): ChatConversation {
        $shouldBroadcast = false;

        $closedConversation = DB::transaction(function () use ($conversation, $closedBy, &$shouldBroadcast) {
            $lockedConversation = ChatConversation::query()
                ->whereKey($conversation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedConversation->status === 'closed') {
                return $lockedConversation;
            }

            if (! in_array(
                $lockedConversation->status,
                ['waiting_agent', 'live_active'],
                true
            )) {
                throw new InvalidArgumentException(
                    'Only live chat conversations can be closed here.'
                );
            }

            $wasLiveActive = $lockedConversation->status === 'live_active'
                && $lockedConversation->assigned_agent_id;

            $lockedConversation->update([
                'status' => 'closed',
                'live_ended_at' => now(),
                'closed_by_id' => $closedBy?->id,
            ]);

            if ($wasLiveActive) {
                $this->closeCurrentLiveChatSession(
                    $lockedConversation,
                    $closedBy ? 'agent' : 'visitor'
                );
            }

            $shouldBroadcast = true;

            $lockedConversation = $lockedConversation->refresh();

            return $lockedConversation;
        });

        if ($shouldBroadcast) {
            $this->broadcastSafely(new LiveChatClosed($closedConversation));
            $session = LiveChatSession::query()
                ->where('conversation_id', $closedConversation->id)
                ->whereNotNull('ended_at')
                ->latest('ended_at')
                ->latest('id')
                ->first();

            if ($session) {
                app(AgentNotificationService::class)->conversationClosed($session);
            }
        }

        return $closedConversation;
    }

    public function pendingRatingForConversation(
        ChatConversation $conversation
    ): ?LiveChatSession {
        return LiveChatSession::query()
            ->where('conversation_id', $conversation->id)
            ->whereNotNull('agent_id')
            ->whereNotNull('ended_at')
            ->where('rating_status', 'pending')
            ->latest('ended_at')
            ->latest('id')
            ->first();
    }

    public function pendingRatingPayload(
        ?LiveChatSession $session
    ): ?array {
        if (! $session) {
            return null;
        }

        $session->loadMissing('agent');

        return [
            'session_id' => $session->id,
            'agent_name' => $session->agent?->name,
            'ended_at' => optional($session->ended_at)->toISOString(),
        ];
    }

    public function submitRating(
        ChatConversation $conversation,
        int $sessionId,
        int $rating,
        ?string $feedback
    ): LiveChatSession {
        return DB::transaction(function () use ($conversation, $sessionId, $rating, $feedback) {
            $session = $this->ratingSessionForConversation(
                $conversation,
                $sessionId
            );

            if ($session->rating_status !== 'pending') {
                throw new InvalidArgumentException(
                    'This live chat session has already been rated.'
                );
            }

            $session->update([
                'rating_status' => 'submitted',
                'rating' => $rating,
                'feedback' => filled($feedback) ? trim($feedback) : null,
                'submitted_at' => now(),
                'skipped_at' => null,
            ]);

            LiveChatRating::preserveFromSession($session->refresh());

            return $session->refresh();
        });
    }

    public function skipRating(
        ChatConversation $conversation,
        int $sessionId
    ): LiveChatSession {
        return DB::transaction(function () use ($conversation, $sessionId) {
            $session = $this->ratingSessionForConversation(
                $conversation,
                $sessionId
            );

            if ($session->rating_status !== 'pending') {
                return $session;
            }

            $session->update([
                'rating_status' => 'skipped',
                'rating' => null,
                'feedback' => null,
                'submitted_at' => null,
                'skipped_at' => now(),
            ]);

            return $session->refresh();
        });
    }

    public function sendAgentMessage(
        ChatConversation $conversation,
        User $agent,
        string $message,
        ?array $attachmentData = null
    ): \App\Models\ChatMessage {
        $this->authorizeAssignedAgentForConversation(
            $agent,
            $conversation
        );

        if ($conversation->status !== 'live_active' || $conversation->mode !== 'live') {
            throw new InvalidArgumentException(
                'Agent messages can only be sent to active live chats.'
            );
        }

        $payload = [
            'sender_type' => 'agent',
            'sender_id' => $agent->id,
            'message' => $message,
        ];

        if ($attachmentData) {
            $payload['attachment'] = $attachmentData['path'];
            $payload['attachment_type'] = $attachmentData['type'];
            $payload['metadata'] = [
                'attachment' => $attachmentData['metadata'],
            ];
        }

        $chatMessage = $conversation->messages()->create($payload);

        $this->broadcastSafely(new LiveChatMessageSent($chatMessage));

        return $chatMessage;
    }

    public function updateAgentChatStatus(
        ChatConversation $conversation,
        User $agent,
        string $status
    ): LiveChatSession {
        if (! in_array($status, LiveChatSession::agentChatStatuses(), true)) {
            throw new InvalidArgumentException(
                'Invalid chat status selected.'
            );
        }

        return DB::transaction(function () use ($conversation, $agent, $status) {
            $lockedConversation = ChatConversation::query()
                ->with('website.settings')
                ->whereKey($conversation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeAssignedAgentForConversation(
                $agent,
                $lockedConversation
            );

            if (
                $lockedConversation->status !== 'live_active'
                || $lockedConversation->mode !== 'live'
            ) {
                throw new InvalidArgumentException(
                    'Chat status can only be changed for active live chats.'
                );
            }

            $session = LiveChatSession::query()
                ->where('conversation_id', $lockedConversation->id)
                ->where('agent_id', $agent->id)
                ->whereNull('ended_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $session) {
                throw new InvalidArgumentException(
                    'No active live chat session found.'
                );
            }

            $session->update([
                'agent_chat_status' => $status,
            ]);

            return $session->refresh();
        });
    }

    public function closeConversationAsAgent(
        ChatConversation $conversation,
        User $agent
    ): ChatConversation {
        $endedConversation = DB::transaction(function () use ($conversation, $agent) {
            $lockedConversation = ChatConversation::query()
                ->with('website.settings')
                ->whereKey($conversation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->authorizeAssignedAgentForConversation(
                $agent,
                $lockedConversation
            );

            if (
                $lockedConversation->status !== 'live_active'
                || $lockedConversation->mode !== 'live'
            ) {
                throw new InvalidArgumentException(
                    'Only active live support sessions can be ended by an agent.'
                );
            }

            $updated = ChatConversation::query()
                ->whereKey($lockedConversation->getKey())
                ->where('status', 'live_active')
                ->where('mode', 'live')
                ->where('assigned_agent_id', $agent->id)
                ->update([
                    'status' => 'active',
                    'mode' => 'ai',
                    'assigned_agent_id' => null,
                    'assigned_at' => null,
                    'handoff_requested_at' => null,
                    'live_ended_at' => now(),
                    'closed_by_id' => null,
                ]);

            if ($updated !== 1) {
                throw new InvalidArgumentException(
                    'This live support session has already ended.'
                );
            }

            $this->closeCurrentLiveChatSession(
                $lockedConversation,
                'agent'
            );

            return $lockedConversation->refresh();
        });

        $this->broadcastSafely(new LiveChatClosed($endedConversation));
        $session = LiveChatSession::query()
            ->where('conversation_id', $endedConversation->id)
            ->whereNotNull('ended_at')
            ->latest('ended_at')
            ->latest('id')
            ->first();

        if ($session) {
            app(AgentNotificationService::class)->conversationClosed($session);
        }

        return $endedConversation;
    }

    protected function authorizeAgentForConversation(
        User $agent,
        ChatConversation $conversation
    ): void {
        if ($agent->role !== 'agent') {
            throw new AuthorizationException(
                'Only agent users can accept live chat conversations.'
            );
        }

        if (! $agent->status) {
            throw new AuthorizationException(
                'Inactive agents cannot accept live chat conversations.'
            );
        }

        if (
            ! $agent->company_id
            || $agent->company_id !== $conversation->website?->company_id
        ) {
            throw new AuthorizationException(
                'This conversation belongs to another company.'
            );
        }

        if (
            ! $agent->assignedWebsites()
                ->whereKey($conversation->website_id)
                ->exists()
        ) {
            throw new AuthorizationException(
                'This agent is not assigned to this website.'
            );
        }

        if (! $this->websiteFeatureService->isLiveChatEnabledForWebsite(
            $conversation->website
        )) {
            throw new InvalidArgumentException(
                'Live Chat is disabled for this website.'
            );
        }
    }

    protected function authorizeAssignedAgentForConversation(
        User $agent,
        ChatConversation $conversation
    ): void {
        $conversation->loadMissing('website.settings');

        $this->authorizeAgentForConversation(
            $agent,
            $conversation
        );

        if ($conversation->assigned_agent_id !== $agent->id) {
            throw new AuthorizationException(
                'This conversation is not assigned to this agent.'
            );
        }
    }

    protected function closeCurrentLiveChatSession(
        ChatConversation $conversation,
        string $endedBy
    ): LiveChatSession {
        $session = LiveChatSession::query()
            ->where('conversation_id', $conversation->id)
            ->where('agent_id', $conversation->assigned_agent_id)
            ->whereNull('ended_at')
            ->latest('id')
            ->lockForUpdate()
            ->first();

        if (! $session) {
            $session = LiveChatSession::create([
                'conversation_id' => $conversation->id,
                'agent_id' => $conversation->assigned_agent_id,
                'started_at' => $conversation->live_started_at
                    ?? $conversation->assigned_at
                    ?? now(),
                'rating_status' => 'pending',
            ]);
        }

        $session->update([
            'ended_at' => now(),
            'ended_by' => $endedBy,
            'agent_chat_status' => null,
        ]);

        LiveChatClosure::preserveFromSession($session->refresh());

        return $session->refresh();
    }

    protected function ratingSessionForConversation(
        ChatConversation $conversation,
        int $sessionId
    ): LiveChatSession {
        $session = LiveChatSession::query()
            ->whereKey($sessionId)
            ->where('conversation_id', $conversation->id)
            ->whereNotNull('agent_id')
            ->whereNotNull('started_at')
            ->whereNotNull('ended_at')
            ->lockForUpdate()
            ->first();

        if (! $session) {
            throw new InvalidArgumentException(
                'No completed live chat session is available for rating.'
            );
        }

        return $session;
    }

    protected function broadcastSafely(object $event): void
    {
        try {
            broadcast($event);
        } catch (\Throwable $exception) {
            Log::warning('Live chat broadcast failed.', [
                'event' => $event::class,
                'message' => $exception->getMessage(),
            ]);
        }
    }
}
