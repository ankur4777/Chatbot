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

        $table->foreignId('company_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('website_id')
              ->constrained()
              ->cascadeOnDelete();

        $table->foreignId('knowledge_category_id')
              ->constrained('knowledge_categories')
              ->cascadeOnDelete();

        $table->string('title');

        $table->string('slug');

        $table->longText('content');

        $table->enum('source_type', [
            'text',
            'faq',
            'url',
            'pdf',
            'docx',
            'csv',
            'json'
        ])->default('text');

        $table->string('source_file')->nullable();

        $table->string('source_url')->nullable();

        $table->boolean('status')->default(true);

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
