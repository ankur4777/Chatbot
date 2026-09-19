<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\AgentActivityLog;
use App\Models\User;
use App\Models\Website;

class LiveChatAvailabilityService
{
    public const ONLINE = 'online';
    public const AWAY = 'away';
    public const OFFLINE = 'offline';

    public function getCompanyAgentStats(int $companyId): array
    {
        $baseQuery = User::query()
            ->where('company_id', $companyId)
            ->where('role', 'agent');

        return [
            'total' => (clone $baseQuery)->count(),
            'online' => (clone $baseQuery)
                ->where('status', true)
                ->where('availability_status', self::ONLINE)
                ->count(),
            'available' => (clone $baseQuery)
                ->where('status', true)
                ->where('availability_status', self::ONLINE)
                ->count(),
        ];
    }

    public function getWebsiteAgentStats(Website $website): array
    {
        $baseQuery = User::query()
            ->where('company_id', $website->company_id)
            ->where('role', 'agent')
            ->whereHas(
                'assignedWebsites',
                fn ($query) => $query->whereKey($website->id)
            );

        return [
            'total' => (clone $baseQuery)->count(),
            'online' => (clone $baseQuery)
                ->where('status', true)
                ->where('availability_status', self::ONLINE)
                ->count(),
            'available' => (clone $baseQuery)
                ->where('status', true)
                ->where('availability_status', self::ONLINE)
                ->count(),
        ];
    }

    public function hasAvailableAgentsForWebsite(Website $website): bool
    {
        return User::query()
            ->where('company_id', $website->company_id)
            ->where('role', 'agent')
            ->where('status', true)
            ->where('availability_status', self::ONLINE)
            ->whereHas(
                'assignedWebsites',
                fn ($query) => $query->whereKey($website->id)
            )
            ->exists();
    }

    public function setAvailability(User $agent, string $status): User
    {
        if (! in_array($status, [
            self::ONLINE,
            self::AWAY,
            self::OFFLINE,
        ], true)) {
            throw new \InvalidArgumentException('Invalid availability status.');
        }

        $now = now();

        match ($status) {
            self::ONLINE => $this->switchToOnline($agent),
            self::AWAY => $this->switchToBreak($agent),
            self::OFFLINE => $this->switchToOffline($agent),
        };

        $agent->update([
            'availability_status' => $status,
            'is_online' => $status === self::ONLINE,
            'last_seen_at' => $now,
        ]);

        return $agent->refresh();
    }

    protected function switchToOnline(User $agent): void
    {
        $this->endOpenActivity($agent, 'break');
        $this->startActivity($agent, 'login');
    }

    protected function switchToBreak(User $agent): void
    {
        $this->endOpenActivity($agent, 'login');
        $this->startActivity($agent, 'break');
    }

    protected function switchToOffline(User $agent): void
    {
        $this->endOpenActivity($agent, 'break');
        $this->endOpenActivity($agent, 'login');
    }

    public function startLoginSession(User $agent): void
    {
        $this->recordPointActivity($agent, 'session_login');
    }

    public function endLoginSession(User $agent): void
    {
        $this->endOpenActivity($agent, 'break');
        $this->endOpenActivity($agent, 'login');
        $this->recordLogoutActivity($agent);
    }

    protected function startActivity(User $agent, string $type): void
    {
        if (
            AgentActivityLog::query()
                ->where('agent_id', $agent->id)
                ->where('type', $type)
                ->whereNull('ended_at')
                ->exists()
        ) {
            return;
        }

        AgentActivityLog::query()->create([
            'agent_id' => $agent->id,
            'type' => $type,
            'started_at' => now(),
        ]);
    }

    protected function recordLogoutActivity(User $agent): void
    {
        $this->recordPointActivity($agent, 'logout');
    }

    protected function recordPointActivity(User $agent, string $type): void
    {
        $now = now();

        AgentActivityLog::query()->create([
            'agent_id' => $agent->id,
            'type' => $type,
            'started_at' => $now,
            'ended_at' => $now,
        ]);
    }

    protected function endOpenActivity(User $agent, string $type): void
    {
        $log = AgentActivityLog::query()
            ->where('agent_id', $agent->id)
            ->where('type', $type)
            ->whereNull('ended_at')
            ->latest('started_at')
            ->first();

        if (! $log) {
            return;
        }

        $now = now();

        AgentActivityLog::query()
            ->whereKey($log->id)
            ->update([
                'ended_at' => $now,
                'updated_at' => $now,
            ]);
    }

    public function countActiveChatsForAgent(User $agent): int
    {
        return ChatConversation::query()
            ->where('assigned_agent_id', $agent->id)
            ->where('status', 'live_active')
            ->whereHas(
                'website',
                fn ($query) => $query->where(
                    'company_id',
                    $agent->company_id
                )
            )
            ->count();
    }

    public function maxActiveChatsForWebsite(Website $website): int
    {
        return max(
            1,
            (int) ($website->settings?->max_active_chats_per_agent ?? 3)
        );
    }
}
