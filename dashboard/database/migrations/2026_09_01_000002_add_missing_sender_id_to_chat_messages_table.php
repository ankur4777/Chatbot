<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('chat_messages', 'sender_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table
                    ->unsignedBigInteger('sender_id')
                    ->nullable()
                    ->after('sender_type');

                $table->index([
                    'sender_type',
                    'sender_id',
                ]);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('chat_messages', 'sender_id')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropIndex([
                    'sender_type',
                    'sender_id',
                ]);

                $table->dropColumn('sender_id');
            });
        }
    }
};
