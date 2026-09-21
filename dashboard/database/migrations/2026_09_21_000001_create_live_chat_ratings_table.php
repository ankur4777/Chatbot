<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('live_chat_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('live_chat_session_id')->nullable()->unique();
            $table->unsignedBigInteger('conversation_id')->nullable()->index();
            $table->unsignedBigInteger('agent_id')->index();
            $table->unsignedBigInteger('company_id')->index();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->unsignedBigInteger('visitor_id')->nullable()->index();
            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();

            $table->index(['agent_id', 'rating']);
            $table->index(['company_id', 'website_id']);
        });

        DB::statement("
            INSERT INTO live_chat_ratings (
                live_chat_session_id,
                conversation_id,
                agent_id,
                company_id,
                website_id,
                visitor_id,
                rating,
                feedback,
                submitted_at,
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
                lcs.rating,
                lcs.feedback,
                COALESCE(lcs.submitted_at, lcs.updated_at),
                NOW(),
                NOW()
            FROM live_chat_sessions lcs
            INNER JOIN users ON users.id = lcs.agent_id
            LEFT JOIN chat_conversations ON chat_conversations.id = lcs.conversation_id
            WHERE lcs.rating_status = 'submitted'
              AND lcs.rating IS NOT NULL
              AND lcs.agent_id IS NOT NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('live_chat_ratings');
    }
};
