<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            UPDATE knowledge_chunks kc
            INNER JOIN knowledge_bases kb
                ON kb.id = kc.knowledge_base_id
            SET kc.website_id = kb.website_id
            WHERE kc.website_id IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};