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
        Schema::table('knowledge_sources', function (Blueprint $table) {

            $table->foreignId('knowledge_category_id')
                ->after('website_id')
                ->constrained()
                ->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('knowledge_sources', function (Blueprint $table) {

            $table->dropConstrainedForeignId('knowledge_category_id');

        });
    }
};