<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_agent', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('website_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('agent_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['website_id', 'agent_id']);
            $table->index(['agent_id', 'website_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_agent');
    }
};
