<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->cascadeOnDelete();
            $table->enum('type', ['login', 'break']);
            $table->dateTime('started_at');
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index(['agent_id', 'type', 'started_at']);
            $table->index(['agent_id', 'type', 'ended_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_activity_logs');
    }
};
