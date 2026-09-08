<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('live_chat_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')
                ->constrained('chat_conversations')
                ->cascadeOnDelete();
            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->string('ended_by')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'ended_at']);
            $table->index(['conversation_id', 'agent_id', 'ended_at']);
        });

        DB::table('chat_conversations')
            ->whereNotNull('assigned_agent_id')
            ->whereNotNull('live_started_at')
            ->whereNotNull('live_ended_at')
            ->orderBy('id')
            ->get()
            ->each(function ($conversation): void {
                DB::table('live_chat_sessions')->insert([
                    'conversation_id' => $conversation->id,
                    'agent_id' => $conversation->assigned_agent_id,
                    'started_at' => $conversation->live_started_at,
                    'ended_at' => $conversation->live_ended_at,
                    'ended_by' => $conversation->closed_by_id ? 'agent' : 'visitor',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_chat_sessions');
    }
};
