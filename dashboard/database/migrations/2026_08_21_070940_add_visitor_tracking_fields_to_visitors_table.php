<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->uuid('visitor_uuid')
                ->nullable()
                ->after('website_id');

            $table->timestamp('first_seen_at')
                ->nullable()
                ->after('user_agent');

            $table->timestamp('last_activity_at')
                ->nullable()
                ->after('first_seen_at');

$table->unique(['website_id', 'visitor_uuid']);        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            $table->dropIndex(['website_id', 'visitor_uuid']);

            $table->dropColumn([
                'visitor_uuid',
                'first_seen_at',
                'last_activity_at',
            ]);
        });
    }
};