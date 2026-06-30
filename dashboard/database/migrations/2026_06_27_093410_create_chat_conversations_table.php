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
    Schema::create('chat_conversations', function (Blueprint $table) {

        $table->id();

        $table->foreignId('website_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('visitor_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->foreignId('assigned_agent_id')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->enum('status', [
        'active',
        'waiting_customer',
        'waiting_agent',
        'resolved',
        'closed',
])->default('active');

        $table->timestamp('started_at')->nullable();

        $table->timestamp('ended_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
