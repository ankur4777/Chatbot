<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('agent_activity_logs')) {
            return;
        }

        DB::statement("
            UPDATE agent_activity_logs
            SET started_at = DATE_SUB(ended_at, INTERVAL 1 SECOND),
                updated_at = CURRENT_TIMESTAMP
            WHERE ended_at IS NOT NULL
                AND ended_at < started_at
        ");
    }

    public function down(): void
    {
        //
    }
};
