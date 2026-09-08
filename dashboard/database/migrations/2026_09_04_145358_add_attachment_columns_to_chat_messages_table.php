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
        if (! Schema::hasColumn('chat_messages', 'attachment')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('message');
            });
        }

        if (! Schema::hasColumn('chat_messages', 'attachment_type')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('attachment_type')->nullable()->after('attachment');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'attachment_type')) {
                $table->dropColumn('attachment_type');
            }

            if (Schema::hasColumn('chat_messages', 'attachment')) {
                $table->dropColumn('attachment');
            }
        });
    }
};
