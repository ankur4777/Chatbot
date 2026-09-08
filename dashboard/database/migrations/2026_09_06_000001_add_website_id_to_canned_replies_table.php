<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('canned_replies', function (Blueprint $table): void {
            if (! Schema::hasColumn('canned_replies', 'website_id')) {
                $table->foreignId('website_id')
                    ->nullable()
                    ->after('company_id')
                    ->constrained()
                    ->nullOnDelete();

                $table->index(['website_id', 'is_active']);
            }
        });
    }

    public function down(): void
    {
        Schema::table('canned_replies', function (Blueprint $table): void {
            if (Schema::hasColumn('canned_replies', 'website_id')) {
                $table->dropConstrainedForeignId('website_id');
            }
        });
    }
};
