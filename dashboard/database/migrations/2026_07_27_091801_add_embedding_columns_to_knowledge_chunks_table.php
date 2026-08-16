<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {

            $table->string('embedding_model')->nullable()->after('chunk_order');

            $table->boolean('embedding_generated')
                ->default(false)
                ->after('embedding_model');

            $table->timestamp('embedding_generated_at')
                ->nullable()
                ->after('embedding_generated');
        });
    }

    public function down(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {

            $table->dropColumn([
                'embedding_model',
                'embedding_generated',
                'embedding_generated_at',
            ]);
        });
    }
};