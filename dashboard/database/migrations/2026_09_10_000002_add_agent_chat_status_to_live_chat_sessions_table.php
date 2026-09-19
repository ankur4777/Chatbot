<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table) {
            $table->string('agent_chat_status')->nullable()->after('skipped_at');
        });
    }

    public function down(): void
    {
        Schema::table('live_chat_sessions', function (Blueprint $table) {
            $table->dropColumn('agent_chat_status');
        });
    }
};
