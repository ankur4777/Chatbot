<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            $table->foreignId('website_id')
                ->nullable()
                ->after('knowledge_base_id')
                ->constrained('websites')
                ->nullOnDelete();
        });

        DB::statement("
            UPDATE knowledge_chunks kc
            INNER JOIN knowledge_bases kb
                ON kb.id = kc.knowledge_base_id
            SET kc.website_id = kb.website_id
            WHERE kc.website_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('knowledge_chunks', function (Blueprint $table) {
            $table->dropForeign(['website_id']);
            $table->dropColumn('website_id');
        });
    }
};
