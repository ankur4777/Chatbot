<?php

namespace App\Filament\Agent\Concerns;

use App\Models\User;

trait HasAgentAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->role === 'agent'
            && $user->status
            && (bool) $user->company_id
            && (bool) $user->company?->status;
    }

    protected function agentCompanyId(): int
    {
        return (int) auth()->user()->company_id;
    }
}
