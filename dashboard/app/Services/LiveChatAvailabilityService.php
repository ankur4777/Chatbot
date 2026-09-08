<?php

namespace App\Services;

use App\Models\ChatConversation;
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

        $agent->update([
            'availability_status' => $status,
            'is_online' => $status === self::ONLINE,
            'last_seen_at' => now(),
        ]);

        return $agent->refresh();
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
