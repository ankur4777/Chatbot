<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (! Schema::hasColumn('visitors', 'details_updated_by')) {
                $table->foreignId('details_updated_by')
                    ->nullable()
                    ->after('last_activity_at')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('visitors', 'details_updated_at')) {
                $table->timestamp('details_updated_at')
                    ->nullable()
                    ->after('details_updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('visitors', function (Blueprint $table) {
            if (Schema::hasColumn('visitors', 'details_updated_by')) {
                $table->dropConstrainedForeignId('details_updated_by');
            }

            if (Schema::hasColumn('visitors', 'details_updated_at')) {
                $table->dropColumn('details_updated_at');
            }
        });
    }
};
