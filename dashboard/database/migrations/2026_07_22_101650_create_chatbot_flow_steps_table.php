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
    Schema::create('chatbot_flow_steps', function (Blueprint $table) {
        $table->id();

        $table->foreignId('chatbot_flow_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->integer('step_order');

        $table->string('step_key')->nullable();

        $table->text('question');

        $table->enum('input_type', [
            'buttons',
            'text',
            'textarea',
            'number',
            'email',
            'phone',
            'date',
            'select',
            'radio',
            'checkbox'
        ]);

        $table->string('placeholder')->nullable();

        $table->boolean('is_required')->default(true);

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_flow_steps');
    }
};
