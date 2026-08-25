<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visitor_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('visitor_id')
                ->constrained('visitors')
                ->cascadeOnDelete();

            $table->string('session_id')->unique();

            $table->ipAddress('ip_address')->nullable();

            $table->timestamp('started_at')->nullable();
$table->timestamp('last_activity_at')->nullable();

            $table->timestamps();

            $table->index(['visitor_id', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visitor_sessions');
    }
};