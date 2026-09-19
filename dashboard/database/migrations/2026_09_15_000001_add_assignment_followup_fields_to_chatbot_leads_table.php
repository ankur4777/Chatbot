<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('chatbot_leads', 'assigned_agent_id')) {
                $table->foreignId('assigned_agent_id')
                    ->nullable()
                    ->after('notes')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('chatbot_leads', 'assigned_by')) {
                $table->foreignId('assigned_by')
                    ->nullable()
                    ->after('assigned_agent_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('chatbot_leads', 'assigned_at')) {
                $table->timestamp('assigned_at')
                    ->nullable()
                    ->after('assigned_by');
            }

            if (! Schema::hasColumn('chatbot_leads', 'followup_status')) {
                $table->string('followup_status')
                    ->default('pending')
                    ->after('assigned_at')
                    ->index();
            }

            if (! Schema::hasColumn('chatbot_leads', 'agent_note')) {
                $table->text('agent_note')
                    ->nullable()
                    ->after('followup_status');
            }

            if (! Schema::hasColumn('chatbot_leads', 'last_contacted_at')) {
                $table->timestamp('last_contacted_at')
                    ->nullable()
                    ->after('agent_note');
            }

            if (! Schema::hasColumn('chatbot_leads', 'next_followup_at')) {
                $table->timestamp('next_followup_at')
                    ->nullable()
                    ->after('last_contacted_at');
            }

            if (! Schema::hasColumn('chatbot_leads', 'resolved_at')) {
                $table->timestamp('resolved_at')
                    ->nullable()
                    ->after('next_followup_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            foreach ([
                'resolved_at',
                'next_followup_at',
                'last_contacted_at',
                'agent_note',
                'followup_status',
                'assigned_at',
            ] as $column) {
                if (Schema::hasColumn('chatbot_leads', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (Schema::hasColumn('chatbot_leads', 'assigned_by')) {
                $table->dropConstrainedForeignId('assigned_by');
            }

            if (Schema::hasColumn('chatbot_leads', 'assigned_agent_id')) {
                $table->dropConstrainedForeignId('assigned_agent_id');
            }
        });
    }
};
