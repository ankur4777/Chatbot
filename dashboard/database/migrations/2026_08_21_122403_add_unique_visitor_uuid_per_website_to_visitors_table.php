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
    // The unique website/visitor UUID constraint is already created in
    // 2026_08_21_070940_add_visitor_tracking_fields_to_visitors_table.php.
    // Keep this migration as a no-op so fresh test databases do not try to
    // create a duplicate unique index.
}

public function down(): void
{
    //
}
};
