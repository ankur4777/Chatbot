<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('chatbot_leads', 'followup_reminder_enabled')) {
                $table->boolean('followup_reminder_enabled')
                    ->default(false)
                    ->after('next_followup_at')
                    ->index();
            }

            if (! Schema::hasColumn('chatbot_leads', 'followup_reminder_at')) {
                $table->timestamp('followup_reminder_at')
                    ->nullable()
                    ->after('followup_reminder_enabled')
                    ->index();
            }

            if (! Schema::hasColumn('chatbot_leads', 'followup_reminder_sent_at')) {
                $table->timestamp('followup_reminder_sent_at')
                    ->nullable()
                    ->after('followup_reminder_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            foreach ([
                'followup_reminder_sent_at',
                'followup_reminder_at',
                'followup_reminder_enabled',
            ] as $column) {
                if (Schema::hasColumn('chatbot_leads', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
