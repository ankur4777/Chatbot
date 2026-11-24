<?php

namespace App\Services;

use App\Models\ChatMessage;
use App\Models\LiveChatSession;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;
use Carbon\CarbonInterface;

class AgentPerformanceMetrics
{
    public static function emptyMetrics(): array
    {
        return [
            'average_handle' => null, 'average_response' => null, 'average_wait' => null,
            'handle_samples' => 0, 'response_samples' => 0, 'wait_samples' => 0,
            'handle_total' => 0, 'response_total' => 0, 'wait_total' => 0,
        ];
    }

    public function forAgent(User $agent): array
    {
        abort_unless($agent->role === 'agent' && $agent->status && $agent->company_id
            && $agent->company?->hasActiveSubscription(), 403);

        return $this->calculate((int) $agent->company_id, null, (int) $agent->id)[$agent->id]
            ?? self::emptyMetrics();
    }

    public function forOwnerWebsite(User $owner, int $websiteId): array
    {
        abort_unless($owner->role === 'owner' && $owner->status && $owner->company_id
            && ! $owner->must_change_password && $owner->company?->hasActiveSubscription()
            && $owner->hasLiveChatAccess(), 403);

        Website::query()->where('company_id', $owner->company_id)->findOrFail($websiteId);

        return $this->calculate((int) $owner->company_id, $websiteId);
    }

    private function calculate(int $companyId, ?int $websiteId, ?int $agentId = null): array
    {
        $now = now();

        // One first human reply per session, attributed to its accepting agent.
        $firstReply = ChatMessage::query()->selectRaw('MIN(chat_messages.created_at)')
            ->whereColumn('chat_messages.conversation_id', 'live_chat_sessions.conversation_id')
            ->where('sender_type', 'agent')
            ->whereColumn('chat_messages.sender_id', 'live_chat_sessions.agent_id')
            ->whereColumn('chat_messages.created_at', '>=', 'live_chat_sessions.started_at')
            ->where('chat_messages.created_at', '<=', $now)
            ->where(function ($query) {
                $query->whereNull('live_chat_sessions.ended_at')
                    ->orWhereColumn('chat_messages.created_at', '<=', 'live_chat_sessions.ended_at');
            });

        // All three metrics use sessions accepted in the rolling last seven days.
        $sessions = LiveChatSession::query()
            ->whereHas('conversation.website', fn ($query) => $query->where('company_id', $companyId))
            ->whereHas('agent', fn ($query) => $query->where('company_id', $companyId)->where('role', 'agent'))
            ->when($websiteId !== null, fn ($query) => $query->whereHas('conversation', fn ($chat) => $chat->where('website_id', $websiteId)))
            ->when($agentId !== null, fn ($query) => $query->where('agent_id', $agentId))
            ->whereBetween('started_at', [$now->copy()->subDays(7), $now])
            ->select(['live_chat_sessions.id', 'agent_id', 'started_at', 'ended_at', 'handoff_requested_at'])
            ->addSelect(['first_reply_at' => $firstReply]);

        $result = [];
        foreach ($sessions->lazyById(500, 'live_chat_sessions.id', 'id') as $session) {
            $id = (int) $session->agent_id;
            $result[$id] ??= self::emptyMetrics();

            if ($session->ended_at && $session->ended_at->lte($now)) {
                $this->addSample($result[$id], 'handle', $session->started_at, $session->ended_at);
            }

            if ($session->first_reply_at !== null) {
                $this->addSample($result[$id], 'response', $session->started_at, Carbon::parse($session->first_reply_at));
            }

            if ($session->handoff_requested_at) {
                $this->addSample($result[$id], 'wait', $session->handoff_requested_at, $session->started_at);
            }
        }

        foreach ($result as &$metrics) {
            foreach (['handle', 'response', 'wait'] as $type) {
                if ($metrics[$type . '_samples'] > 0) {
                    $metrics['average_' . $type] = (int) round($metrics[$type . '_total'] / $metrics[$type . '_samples']);
                }
            }
        }
        unset($metrics);

        return $result;
    }

    private function addSample(array &$metrics, string $type, CarbonInterface $start, CarbonInterface $end): void
    {
        $seconds = $end->getTimestamp() - $start->getTimestamp();

        if ($seconds >= 0) {
            $metrics[$type . '_total'] += $seconds;
            $metrics[$type . '_samples']++;
        }
    }
}
