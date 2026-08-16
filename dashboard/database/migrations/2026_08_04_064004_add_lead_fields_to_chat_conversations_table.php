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
       Schema::table('chat_conversations', function (Blueprint $table) {

        $table->string('lead_step')
            ->nullable()
            ->after('status');

        $table->boolean('lead_completed')
            ->default(false)
            ->after('lead_step');

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('chat_conversations', function (Blueprint $table) {

        $table->dropColumn([
            'lead_step',
            'lead_completed',
        ]);

    });
    }
};
