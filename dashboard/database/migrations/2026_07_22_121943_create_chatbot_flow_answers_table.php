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
    Schema::create('chatbot_flow_answers', function (Blueprint $table) {
        $table->id();

        $table->foreignId('conversation_id')
            ->constrained('chat_conversations')
            ->cascadeOnDelete();

        $table->foreignId('chatbot_flow_step_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->text('answer')->nullable();

        $table->timestamps();

        $table->unique([
            'conversation_id',
            'chatbot_flow_step_id'
        ]);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_answers');
    }
};
