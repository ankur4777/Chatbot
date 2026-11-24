<?php

namespace App\Filament\Client;

use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;

class SubscriptionGlobalSearchProvider extends DefaultGlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $user = auth('client')->user();

        if (! $user || $user->role !== 'owner' || ! $user->status || $user->must_change_password || ! $user->company?->hasActiveSubscription()) {
            return null;
        }

        return parent::getResults($query);
    }
}
