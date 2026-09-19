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

        DB::statement('ALTER TABLE agent_activity_logs MODIFY started_at DATETIME NOT NULL');
        DB::statement('ALTER TABLE agent_activity_logs MODIFY ended_at DATETIME NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('agent_activity_logs')) {
            return;
        }

        DB::statement('ALTER TABLE agent_activity_logs MODIFY started_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE agent_activity_logs MODIFY ended_at TIMESTAMP NULL');
    }
};
