public function up(): void
{
    Schema::table('chat_messages', function (Blueprint $table) {
        $table->foreignId('conversation_id')
            ->after('id')
            ->constrained('chat_conversations')
            ->cascadeOnDelete();

        $table->string('sender_type')->after('conversation_id');
        $table->unsignedBigInteger('sender_id')->nullable()->after('sender_type');
        $table->longText('message')->nullable()->after('sender_id');
        $table->string('attachment')->nullable()->after('message');
        $table->string('attachment_type')->nullable()->after('attachment');
        $table->json('metadata')->nullable()->after('attachment_type');
        $table->timestamp('read_at')->nullable()->after('metadata');

        $table->index(['conversation_id', 'created_at']);
        $table->index(['sender_type', 'sender_id']);
    });
}

public function down(): void
{
    Schema::table('chat_messages', function (Blueprint $table) {
        $table->dropForeign(['conversation_id']);

        $table->dropIndex(['conversation_id', 'created_at']);
        $table->dropIndex(['sender_type', 'sender_id']);

        $table->dropColumn([
            'conversation_id',
            'sender_type',
            'sender_id',
            'message',
            'attachment',
            'attachment_type',
            'metadata',
            'read_at',
        ]);
    });
}