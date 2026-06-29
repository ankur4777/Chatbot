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
    Schema::create('knowledge_bases', function (Blueprint $table) {

        $table->id();

        $table->foreignId('knowledge_category_id')
            ->constrained()
            ->cascadeOnDelete();

        $table->string('title');

        $table->longText('content');

        $table->enum('source_type', [
            'text',
            'pdf',
            'url',
            'faq',
            'docx',
            'csv',
            'json'
        ])->default('text');

        $table->string('source_file')->nullable();

        $table->string('source_url')->nullable();

        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_bases');
    }
};
