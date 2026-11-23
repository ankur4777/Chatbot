<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table): void {
            $table->string('legal_name')->nullable()->after('name');
            $table->string('county_name')->nullable()->after('address');
            $table->string('country_code', 8)->nullable()->after('county_name');
            $table->timestamp('subscription_expires_at')->nullable()->after('status');
            $table->unsignedTinyInteger('conversation_retention_months')->default(1)->after('subscription_expires_at');
        });

        DB::table('companies')->whereNull('legal_name')->update([
            'legal_name' => DB::raw('name'),
        ]);

        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('must_change_password')->default(false)->after('status');
        });

        DB::table('users')
            ->where('role', 'owner')
            ->update(['must_change_password' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('must_change_password');
        });

        Schema::table('companies', function (Blueprint $table): void {
            $table->dropColumn([
                'legal_name',
                'county_name',
                'country_code',
                'subscription_expires_at',
                'conversation_retention_months',
            ]);
        });
    }
};
