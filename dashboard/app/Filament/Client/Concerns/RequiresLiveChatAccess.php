<?php

namespace App\Filament\Client\Concerns;

trait RequiresLiveChatAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user?->role === 'owner'
            && $user->hasLiveChatAccess();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    protected function clientCompanyId(): int
    {
        return (int) auth()->user()->company_id;
    }
}
