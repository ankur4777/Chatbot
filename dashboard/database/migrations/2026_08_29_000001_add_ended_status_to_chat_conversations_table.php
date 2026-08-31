<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE chat_conversations MODIFY status ENUM('active', 'waiting_customer', 'waiting_agent', 'resolved', 'closed', 'ended') DEFAULT 'active'"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE chat_conversations MODIFY status ENUM('active', 'waiting_customer', 'waiting_agent', 'resolved', 'closed') DEFAULT 'active'"
        );
    }
};
