<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use Illuminate\Console\Command;

class EndInactiveConversations extends Command
{
    protected $signature = 'chatbot:end-inactive';

    protected $description =
        'End chatbot conversations that have been inactive for 5 minutes';

    public function handle(): int
    {
        $cutoff = now()->subMinutes(5);

        $conversations = ChatConversation::query()
            ->where('status', 'active')

            // Conversation must already be older than timeout.
            ->where('created_at', '<=', $cutoff)

            // No message activity during last 5 minutes.
            ->whereDoesntHave(
                'messages',
                fn ($query) =>
                    $query->where(
                        'created_at',
                        '>',
                        $cutoff
                    )
            )
            ->get();

        foreach ($conversations as $conversation) {
            $conversation->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
        }

        $this->info(
            "{$conversations->count()} inactive conversations ended."
        );

        return self::SUCCESS;
    }
}