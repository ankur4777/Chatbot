<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // The chat_messages table already contains these columns
        // in the original create_chat_messages_table migration.
    }

    public function down(): void
    {
        // Nothing to reverse.
    }
};
