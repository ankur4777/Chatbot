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
    Schema::create('api_keys', function (Blueprint $table) {

        $table->id();

        $table->foreignId('website_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('key')->unique();

        $table->enum('type', [
            'widget',
            'api',
        ])->default('widget');

        $table->boolean('status')->default(true);

        $table->timestamp('last_used_at')->nullable();

        $table->timestamp('expires_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_keys');
    }
};
