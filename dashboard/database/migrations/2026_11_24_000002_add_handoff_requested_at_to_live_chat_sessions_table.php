<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table): void {
            $table->timestamp('handoff_requested_at')->nullable();
        });

        // Only recover a timestamp when the session matches the conversation's
        // currently recorded live episode. Never guess a historical wait time.
        DB::table('live_chat_sessions as sessions')
            ->join('chat_conversations as conversations', 'conversations.id', '=', 'sessions.conversation_id')
            ->whereNotNull('conversations.handoff_requested_at')
            ->whereColumn('sessions.agent_id', 'conversations.assigned_agent_id')
            ->whereColumn('sessions.started_at', 'conversations.live_started_at')
            ->whereColumn('conversations.handoff_requested_at', '<=', 'sessions.started_at')
            ->select('sessions.id as id', 'conversations.handoff_requested_at')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('live_chat_sessions')->where('id', $row->id)
                        ->update(['handoff_requested_at' => $row->handoff_requested_at]);
                }
            }, 'sessions.id', 'id');
    }

    public function down(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table): void {
            $table->dropColumn('handoff_requested_at');
        });
    }
};
