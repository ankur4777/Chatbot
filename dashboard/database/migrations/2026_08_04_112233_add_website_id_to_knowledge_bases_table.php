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
    Schema::table('knowledge_bases', function (Blueprint $table) {

        $table->foreignId('website_id')
    ->nullable()
    ->after('knowledge_source_id')
    ->constrained()
    ->nullOnDelete();

    });
}
    /**
     * Reverse the migrations.
     */
   public function down(): void
{
    Schema::table('knowledge_bases', function (Blueprint $table) {

        $table->dropConstrainedForeignId('website_id');

    });
}
};
