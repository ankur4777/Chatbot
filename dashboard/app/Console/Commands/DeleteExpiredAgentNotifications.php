<?php

namespace App\Console\Commands;

use App\Models\AgentNotification;
use Illuminate\Console\Command;

class DeleteExpiredAgentNotifications extends Command
{
    protected $signature = 'agent:delete-expired-notifications';

    protected $description = 'Delete agent notifications older than one day.';

    public function handle(): int
    {
        $deleted = AgentNotification::query()
            ->where('created_at', '<', today())
            ->delete();

        $this->info("Deleted {$deleted} expired agent notifications.");

        return self::SUCCESS;
    }
}
