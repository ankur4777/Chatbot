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
    Schema::table('companies', function (Blueprint $table) {
        $table->string('owner_name')->nullable()->after('name');
        $table->string('gst_number')->nullable()->after('phone');
        $table->text('address')->nullable()->after('gst_number');
    });
}

public function down(): void
{
    Schema::table('companies', function (Blueprint $table) {
        $table->dropColumn([
            'owner_name',
            'gst_number',
            'address',
        ]);
    });
}
};
