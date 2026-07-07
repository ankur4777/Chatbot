<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_sources', function (Blueprint $table) {

            $table->id();

            $table->foreignId('website_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', [
                'website',
                'pdf',
                'json',
                'docx',
                'txt',
                'faq',
            ]);

            $table->string('title');

            $table->text('source')->nullable();

            $table->enum('status', [
                'pending',
                'processing',
                'completed',
                'failed',
            ])->default('pending');

            $table->integer('pages')->default(0);

            $table->integer('chunks')->default(0);

            $table->text('error')->nullable();

            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_sources');
    }
};