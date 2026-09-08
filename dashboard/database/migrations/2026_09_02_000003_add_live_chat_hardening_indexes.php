<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->index(
                ['website_id', 'status', 'handoff_requested_at'],
                'chat_conversations_waiting_queue_index'
            );
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->index(
                ['company_id', 'role', 'availability_status'],
                'users_live_chat_availability_index'
            );
        });

        Schema::table('chatbot_leads', function (Blueprint $table): void {
            $table->index(
                ['website_id', 'source'],
                'chatbot_leads_website_source_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            $table->dropIndex('chatbot_leads_website_source_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_live_chat_availability_index');
        });

        Schema::table('chat_conversations', function (Blueprint $table): void {
            $table->dropIndex('chat_conversations_waiting_queue_index');
        });
    }
};
