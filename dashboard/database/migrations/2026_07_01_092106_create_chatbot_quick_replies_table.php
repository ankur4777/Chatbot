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
    Schema::create('chatbot_quick_replies', function (Blueprint $table) {

        $table->id();

        $table->foreignId('website_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('label');

        $table->string('value');

        $table->string('icon')->nullable();

        $table->integer('sort_order')->default(0);

        $table->boolean('is_active')->default(true);

        $table->timestamps();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_quick_replies');
    }
};
