<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canned_replies', function (Blueprint $table): void {
            if (! Schema::hasColumn('canned_replies', 'agent_id')) {
                $table->foreignId('agent_id')
                    ->nullable()
                    ->after('website_id')
                    ->constrained('users')
                    ->cascadeOnDelete();

                $table->index(
                    ['agent_id', 'website_id', 'is_active'],
                    'canned_replies_agent_website_active_index'
                );
            }
        });
    }

    public function down(): void
    {
        Schema::table('canned_replies', function (Blueprint $table): void {
            if (Schema::hasColumn('canned_replies', 'agent_id')) {
                $table->dropIndex('canned_replies_agent_website_active_index');
                $table->dropConstrainedForeignId('agent_id');
            }
        });
    }
};
