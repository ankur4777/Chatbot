<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add nullable column
        Schema::table('websites', function (Blueprint $table) {
            $table->string('widget_key')->nullable()->after('domain');
        });

        // Step 2: Generate widget keys for existing websites
        $websites = DB::table('websites')->get();

        foreach ($websites as $website) {

            do {
                $widgetKey = 'WGT_' . Str::upper(Str::random(16));
            } while (
                DB::table('websites')
                    ->where('widget_key', $widgetKey)
                    ->exists()
            );

            DB::table('websites')
                ->where('id', $website->id)
                ->update([
                    'widget_key' => $widgetKey
                ]);
        }

        // Step 3: Make widget_key unique
        Schema::table('websites', function (Blueprint $table) {
            $table->unique('widget_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $table->dropUnique(['widget_key']);
            $table->dropColumn('widget_key');
        });
    }
};