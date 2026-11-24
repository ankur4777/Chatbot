<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\LiveChatSession;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;

class LiveInboxMetrics
{
    public function forWebsite(User $owner, ?int $websiteId): array
    {
        abort_unless($owner->role === 'owner' && $owner->status
            && $owner->company_id && ! $owner->must_change_password
            && $owner->company?->hasActiveSubscription() && $owner->hasLiveChatAccess(), 403);

        $empty = [
            'waiting' => 0, 'active' => 0, 'online' => 0, 'away' => 0,
            'offline' => 0, 'disabled' => 0, 'average_wait' => null,
            'average_response' => null, 'response_samples' => 0, 'wait_samples' => 0,
            'agents' => collect(), 'unassigned_active' => 0,
        ];

        if ($websiteId === null) {
            return $empty;
        }

        $website = Website::query()->where('company_id', $owner->company_id)->findOrFail($websiteId);
        $now = now();

        // Match the Waiting Chats and Active Chats pages' existing 30-day activity window.
        $open = ChatConversation::query()->where('website_id', $website->id)
            ->where('updated_at', '>=', $now->copy()->subDays(30));
        $waiting = (clone $open)->where('status', 'waiting_agent');
        $active = (clone $open)->where('status', 'live_active');

        $waitTotal = 0;
        $waitSamples = 0;
        foreach ((clone $waiting)->whereNotNull('handoff_requested_at')
            ->select(['id', 'handoff_requested_at'])->lazyById(500) as $conversation) {
            $seconds = $now->getTimestamp() - $conversation->handoff_requested_at->getTimestamp();
            if ($seconds >= 0) {
                $waitTotal += $seconds;
                $waitSamples++;
            }
        }

        // First human reply after the agent accepts a live session, not an AI reply.
        // Include current and completed sessions started in the last seven days.
        $firstReply = ChatMessage::query()->selectRaw('MIN(chat_messages.created_at)')
            ->whereColumn('chat_messages.conversation_id', 'live_chat_sessions.conversation_id')
            ->where('sender_type', 'agent')
            ->whereColumn('chat_messages.sender_id', 'live_chat_sessions.agent_id')
            ->whereColumn('chat_messages.created_at', '>=', 'live_chat_sessions.started_at')
            ->where(function ($query) {
                $query->whereNull('live_chat_sessions.ended_at')
                    ->orWhereColumn('chat_messages.created_at', '<=', 'live_chat_sessions.ended_at');
            });

        $sessions = LiveChatSession::query()
            ->whereHas('conversation', fn ($query) => $query->where('website_id', $website->id))
            ->whereHas('agent', fn ($query) => $query->where('company_id', $owner->company_id))
            ->whereBetween('started_at', [$now->copy()->subDays(7), $now])
            ->select(['live_chat_sessions.id', 'started_at'])
            ->addSelect(['first_reply_at' => $firstReply]);

        $responseTotal = 0;
        $responseSamples = 0;
        foreach ($sessions->lazyById(500, 'live_chat_sessions.id', 'id') as $session) {
            if ($session->first_reply_at === null) {
                continue;
            }
            $seconds = Carbon::parse($session->first_reply_at)->getTimestamp() - $session->started_at->getTimestamp();
            if ($seconds >= 0) {
                $responseTotal += $seconds;
                $responseSamples++;
            }
        }

        $workloads = (clone $active)->whereNotNull('assigned_agent_id')
            ->selectRaw('assigned_agent_id, COUNT(*) as chat_count')
            ->groupBy('assigned_agent_id')->pluck('chat_count', 'assigned_agent_id');

        $agents = User::query()->where('company_id', $owner->company_id)->where('role', 'agent')
            ->whereHas('assignedWebsites', fn ($query) => $query->whereKey($website->id))
            ->orderBy('name')->get(['id', 'name', 'status', 'availability_status'])
            ->map(function (User $agent) use ($workloads): array {
                $status = ! $agent->status ? 'disabled' : match ($agent->availability_status) {
                    'online' => 'online', 'away' => 'away', default => 'offline',
                };

                return ['id' => $agent->id, 'name' => $agent->name,
                    'status' => $status, 'chats' => (int) ($workloads[$agent->id] ?? 0)];
            });

        $counts = $agents->countBy('status');
        $activeCount = $active->count();

        return [
            'waiting' => $waiting->count(), 'active' => $activeCount,
            'online' => (int) ($counts['online'] ?? 0),
            'away' => (int) ($counts['away'] ?? 0),
            'offline' => (int) ($counts['offline'] ?? 0),
            'disabled' => (int) ($counts['disabled'] ?? 0),
            'average_wait' => $waitSamples ? (int) round($waitTotal / $waitSamples) : null,
            'average_response' => $responseSamples ? (int) round($responseTotal / $responseSamples) : null,
            'wait_samples' => $waitSamples, 'response_samples' => $responseSamples,
            'agents' => $agents->sortByDesc('chats')->values(),
            'unassigned_active' => max(0, $activeCount - $agents->sum('chats')),
        ];
    }

    public static function duration(?int $seconds): string
    {
        if ($seconds === null) {
            return '—';
        }

        if ($seconds >= 3600) {
            return intdiv($seconds, 3600) . 'h ' . intdiv($seconds % 3600, 60) . 'm';
        }

        if ($seconds >= 60) {
            return intdiv($seconds, 60) . 'm ' . ($seconds % 60) . 's';
        }

        return $seconds . 's';
    }
}
