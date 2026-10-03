<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('website_settings', 'agent_dashboard_logo')) {
                $table->string('agent_dashboard_logo')->nullable()->after('max_agents_per_website');
            }
        });
    }

    public function down(): void
    {
        Schema::table('website_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('website_settings', 'agent_dashboard_logo')) {
                $table->dropColumn('agent_dashboard_logo');
            }
        });
    }
};
