<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'availability_status')) {
                $table->enum('availability_status', ['online', 'away', 'offline'])
                    ->default('offline')
                    ->after('is_online')
                    ->index();
            }
        });

        Schema::table('website_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('website_settings', 'offline_behavior')) {
                $table->enum('offline_behavior', ['show_offline_form', 'hide_button'])
                    ->default('show_offline_form')
                    ->after('show_connect_agent');
            }

            if (! Schema::hasColumn('website_settings', 'max_active_chats_per_agent')) {
                $table->unsignedTinyInteger('max_active_chats_per_agent')
                    ->default(3)
                    ->after('offline_behavior');
            }

            if (! Schema::hasColumn('website_settings', 'offline_message')) {
                $table->text('offline_message')
                    ->nullable()
                    ->after('max_active_chats_per_agent');
            }

            if (! Schema::hasColumn('website_settings', 'waiting_message')) {
                $table->text('waiting_message')
                    ->nullable()
                    ->after('offline_message');
            }
        });

        Schema::table('chatbot_leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('chatbot_leads', 'source')) {
                $table->string('source')
                    ->default('chatbot')
                    ->after('conversation_id')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chatbot_leads', function (Blueprint $table): void {
            if (Schema::hasColumn('chatbot_leads', 'source')) {
                $table->dropColumn('source');
            }
        });

        Schema::table('website_settings', function (Blueprint $table): void {
            foreach ([
                'waiting_message',
                'offline_message',
                'max_active_chats_per_agent',
                'offline_behavior',
            ] as $column) {
                if (Schema::hasColumn('website_settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('users', function (Blueprint $table): void {
            if (Schema::hasColumn('users', 'availability_status')) {
                $table->dropColumn('availability_status');
            }
        });
    }
};
