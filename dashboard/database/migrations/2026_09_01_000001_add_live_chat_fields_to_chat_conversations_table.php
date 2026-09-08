<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE chat_conversations MODIFY status ENUM('active', 'waiting_customer', 'waiting_agent', 'live_active', 'resolved', 'closed', 'ended') DEFAULT 'active'"
            );
        }

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_conversations', 'mode')) {
                $table->string('mode')
                    ->default('ai')
                    ->after('status');
            }

            if (! Schema::hasColumn('chat_conversations', 'handoff_requested_at')) {
                $table->timestamp('handoff_requested_at')
                    ->nullable()
                    ->after('lead_completed');
            }

            if (! Schema::hasColumn('chat_conversations', 'assigned_at')) {
                $table->timestamp('assigned_at')
                    ->nullable()
                    ->after('handoff_requested_at');
            }

            if (! Schema::hasColumn('chat_conversations', 'live_started_at')) {
                $table->timestamp('live_started_at')
                    ->nullable()
                    ->after('assigned_at');
            }

            if (! Schema::hasColumn('chat_conversations', 'live_ended_at')) {
                $table->timestamp('live_ended_at')
                    ->nullable()
                    ->after('live_started_at');
            }

            if (! Schema::hasColumn('chat_conversations', 'closed_by_id')) {
                $table->foreignId('closed_by_id')
                    ->nullable()
                    ->after('live_ended_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            $table->index('status');
            $table->index('assigned_agent_id');
            $table->index('website_id');
            $table->index([
                'website_id',
                'status',
                'assigned_agent_id',
            ], 'chat_conversations_live_queue_index');
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            $table->dropIndex('chat_conversations_live_queue_index');
            $table->dropIndex(['website_id']);
            $table->dropIndex(['assigned_agent_id']);
            $table->dropIndex(['status']);

            if (Schema::hasColumn('chat_conversations', 'closed_by_id')) {
                $table->dropConstrainedForeignId('closed_by_id');
            }

            $table->dropColumn([
                'mode',
                'handoff_requested_at',
                'assigned_at',
                'live_started_at',
                'live_ended_at',
            ]);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE chat_conversations MODIFY status ENUM('active', 'waiting_customer', 'waiting_agent', 'resolved', 'closed', 'ended') DEFAULT 'active'"
            );
        }
    }
};
