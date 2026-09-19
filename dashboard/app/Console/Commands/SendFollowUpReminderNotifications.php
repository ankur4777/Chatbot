<?php

namespace App\Console\Commands;

use App\Models\ChatbotLead;
use App\Services\AgentNotificationService;
use Illuminate\Console\Command;

class SendFollowUpReminderNotifications extends Command
{
    protected $signature = 'agent:send-follow-up-reminders';

    protected $description = 'Create due follow-up reminder notifications for assigned missed chats.';

    public function handle(AgentNotificationService $notifications): int
    {
        $created = 0;

        ChatbotLead::query()
            ->with(['visitor', 'website'])
            ->where('source', 'live_chat_offline_request')
            ->whereNotNull('assigned_agent_id')
            ->where('followup_reminder_enabled', true)
            ->whereNotNull('followup_reminder_at')
            ->whereNull('followup_reminder_sent_at')
            ->where('followup_reminder_at', '<=', now())
            ->whereNotIn('followup_status', ['resolved', 'unable_to_reach'])
            ->chunkById(100, function ($leads) use ($notifications, &$created): void {
                foreach ($leads as $lead) {
                    if (! $lead->next_followup_at || $lead->next_followup_at->ne($lead->followup_reminder_at)) {
                        $lead->forceFill([
                            'followup_reminder_enabled' => false,
                            'followup_reminder_at' => null,
                            'followup_reminder_sent_at' => null,
                        ])->save();
                        continue;
                    }

                    $notifications->followUpReminder($lead);

                    $lead->forceFill([
                        'followup_reminder_sent_at' => now(),
                    ])->save();

                    $created++;
                }
            });

        $this->info("Created {$created} follow-up reminder notifications.");

        return self::SUCCESS;
    }
}
