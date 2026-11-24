<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\User;
use App\Models\Website;

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

        $performance = app(AgentPerformanceMetrics::class)->forOwnerWebsite($owner, (int) $website->id);
        $responseTotal = array_sum(array_column($performance, 'response_total'));
        $responseSamples = array_sum(array_column($performance, 'response_samples'));

        $workloads = (clone $active)->whereNotNull('assigned_agent_id')
            ->selectRaw('assigned_agent_id, COUNT(*) as chat_count')
            ->groupBy('assigned_agent_id')->pluck('chat_count', 'assigned_agent_id');

        $agents = User::query()->where('company_id', $owner->company_id)->where('role', 'agent')
            ->whereHas('assignedWebsites', fn ($query) => $query->whereKey($website->id))
            ->orderBy('name')->get(['id', 'name', 'status', 'availability_status'])
            ->map(function (User $agent) use ($workloads, $performance): array {
                $status = ! $agent->status ? 'disabled' : match ($agent->availability_status) {
                    'online' => 'online', 'away' => 'away', default => 'offline',
                };

                return ['id' => $agent->id, 'name' => $agent->name,
                    'status' => $status, 'chats' => (int) ($workloads[$agent->id] ?? 0),
                    'performance' => $performance[$agent->id] ?? AgentPerformanceMetrics::emptyMetrics()];
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
