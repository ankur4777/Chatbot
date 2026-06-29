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
    Schema::create('website_settings', function (Blueprint $table) {

        $table->id();

        $table->foreignId('website_id')
            ->constrained()
            ->cascadeOnDelete();

        // Basic
        $table->string('chatbot_name')->default('AI Assistant');
        $table->string('welcome_message')->nullable();
        $table->string('placeholder')->default('Type your message...');
        $table->string('language')->default('en');

        // AI
        $table->string('model')->default('qwen');
        $table->decimal('temperature', 3, 2)->default(0.70);

        // Widget
        $table->string('primary_color')->default('#2563eb');
        $table->enum('position', ['left', 'right'])->default('right');

        // Features
        $table->boolean('enable_chatbot')->default(true);
        $table->boolean('enable_live_chat')->default(false);
        $table->boolean('show_connect_agent')->default(false);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_settings');
    }
};
