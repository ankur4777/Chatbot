<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table) {
            $table->string('rating_status')->default('skipped')->after('note_updated_at');
            $table->unsignedTinyInteger('rating')->nullable()->after('rating_status');
            $table->text('feedback')->nullable()->after('rating');
            $table->timestamp('submitted_at')->nullable()->after('feedback');
            $table->timestamp('skipped_at')->nullable()->after('submitted_at');

            $table->index(['conversation_id', 'rating_status']);
        });
    }

    public function down(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table) {
            $table->dropIndex(['conversation_id', 'rating_status']);
            $table->dropColumn([
                'rating_status',
                'rating',
                'feedback',
                'submitted_at',
                'skipped_at',
            ]);
        });
    }
};
