<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chat_closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_chat_session_id')->nullable()->unique();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->unsignedBigInteger('visitor_id')->nullable()->index();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->index();
            $table->string('ended_by')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'ended_at']);
            $table->index(['company_id', 'website_id']);
        });

        DB::statement("
            INSERT INTO live_chat_closures (
                live_chat_session_id,
                conversation_id,
                agent_id,
                company_id,
                website_id,
                visitor_id,
                started_at,
                ended_at,
                ended_by,
                created_at,
                updated_at
            )
            SELECT
                lcs.id,
                lcs.conversation_id,
                lcs.agent_id,
                users.company_id,
                chat_conversations.website_id,
                chat_conversations.visitor_id,
                lcs.started_at,
                lcs.ended_at,
                lcs.ended_by,
                NOW(),
                NOW()
            FROM live_chat_sessions lcs
            INNER JOIN users ON users.id = lcs.agent_id
            LEFT JOIN chat_conversations ON chat_conversations.id = lcs.conversation_id
            WHERE lcs.ended_at IS NOT NULL
              AND lcs.agent_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_closures');
    }
};
