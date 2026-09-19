<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "ALTER TABLE agent_activity_logs MODIFY type ENUM('login', 'break', 'logout', 'session_login') NOT NULL"
        );
    }

    public function down(): void
    {
        DB::table('agent_activity_logs')
            ->where('type', 'session_login')
            ->delete();

        DB::statement(
            "ALTER TABLE agent_activity_logs MODIFY type ENUM('login', 'break', 'logout') NOT NULL"
        );
    }
};
