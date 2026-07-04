<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('chat_messages', function (Blueprint $table) {

        $table->foreignId('conversation_id')
              ->constrained('chat_conversations')
              ->cascadeOnDelete()
              ->after('id');

        $table->enum('sender_type', [
            'visitor',
            'bot',
            'agent',
        ])->after('conversation_id');

        $table->text('message')->after('sender_type');

        $table->json('metadata')->nullable()->after('message');

        $table->timestamp('read_at')->nullable()->after('metadata');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::table('chat_messages', function (Blueprint $table) {

        $table->dropForeign(['conversation_id']);

        $table->dropColumn([
            'conversation_id',
            'sender_type',
            'message',
            'metadata',
            'read_at',
        ]);

    });
}
};
