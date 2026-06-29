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
    Schema::table('users', function (Blueprint $table) {

        $table->foreignId('company_id')
            ->nullable()
            ->after('id')
            ->constrained()
            ->nullOnDelete();

        $table->enum('role', [
            'super_admin',
            'owner',
            'agent',
        ])->default('owner')->after('password');

        $table->boolean('status')->default(true);

        $table->boolean('is_online')->default(false);

        $table->timestamp('last_seen_at')->nullable();

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
