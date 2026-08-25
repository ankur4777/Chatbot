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
    Schema::table('visitors', function (Blueprint $table) {
        $table->unique(
            ['website_id', 'visitor_uuid'],
            'visitors_website_visitor_uuid_unique'
        );
    });
}

public function down(): void
{
    Schema::table('visitors', function (Blueprint $table) {
        $table->dropUnique(
            'visitors_website_visitor_uuid_unique'
        );
    });
}
};
