<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatbotLead;
use App\Models\LiveChatSession;
use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CleanupExpiredChatData extends Command
{
    protected $signature = 'chatbot:cleanup-expired-data {--dry-run : Show what would be deleted without deleting anything}';

    protected $description = 'Delete expired chat, missed chat, chatbot conversation, and visitor data using safe retention rules.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $closedChatStats = $this->cleanupClosedChats($dryRun);
        $chatbotStats = $this->cleanupChatbotConversations($dryRun);
        $missedChatStats = $this->cleanupMissedChats($dryRun);
        $visitorStats = $this->cleanupVisitors($dryRun);

        $message = sprintf(
            '%s expired data cleanup complete. Closed conversations: %d, closed sessions: %d, chatbot conversations: %d, missed chats: %d, attachments: %d, visitors: %d.',
            $dryRun ? 'Dry-run' : 'Deleted',
            $closedChatStats['conversations'],
            $closedChatStats['sessions'],
            $chatbotStats['conversations'],
            $missedChatStats['missed_chats'],
            $closedChatStats['attachments'] + $chatbotStats['attachments'],
            $visitorStats['visitors'],
        );

        $this->info($message);
        Log::info($message, [
            'dry_run' => $dryRun,
            'closed_chats' => $closedChatStats,
            'chatbot_conversations' => $chatbotStats,
            'missed_chats' => $missedChatStats,
            'visitors' => $visitorStats,
        ]);

        return self::SUCCESS;
    }

    protected function cleanupClosedChats(bool $dryRun): array
    {
        $retentionDays = (int) config('data-retention.closed_chats.retention_days', 30);
        $chunkSize = max(1, (int) config('data-retention.closed_chats.chunk_size', 100));
        $cutoff = now()->subDays($retentionDays);
        $deletedConversations = 0;
        $deletedSessions = 0;
        $deletedAttachments = 0;

        $this->info("Closed chat cutoff: {$cutoff->toDateTimeString()}");

        $query = ChatConversation::query()
            ->where('status', 'closed')
            // Prefer explicit live/ended timestamps, then fall back to updated_at.
            ->whereRaw(
                'COALESCE(live_ended_at, ended_at, updated_at) < ?',
                [$cutoff->toDateTimeString()]
            );

        $query
            ->select('id')
            ->orderBy('id')
            ->chunkById($chunkSize, function ($conversations) use ($dryRun, &$deletedConversations, &$deletedAttachments): void {
                $conversationIds = $conversations->pluck('id')->all();

                if ($conversationIds === []) {
                    return;
                }

                $attachmentPaths = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->whereNotNull('attachment')
                    ->pluck('attachment')
                    ->filter()
                    ->unique()
                    ->values();

                if ($dryRun) {
                    $deletedConversations += count($conversationIds);
                    $deletedAttachments += $attachmentPaths->count();

                    return;
                }

                foreach ($attachmentPaths as $path) {
                    if (Storage::disk('local')->delete($path)) {
                        $deletedAttachments++;
                    }
                }

                DB::transaction(function () use ($conversationIds, &$deletedConversations): void {
                    // chat_messages, chatbot_flow_answers, chatbot_leads, and live_chat_sessions
                    // are cascade-deleted by their foreign keys when the closed conversation is deleted.
                    $deletedConversations += ChatConversation::query()
                        ->whereIn('id', $conversationIds)
                        ->where('status', 'closed')
                        ->delete();
                });
            });

        LiveChatSession::query()
            ->select('id')
            ->whereNotNull('ended_at')
            ->where('ended_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($sessions) use ($dryRun, &$deletedSessions): void {
                $sessionIds = $sessions->pluck('id')->all();

                if ($sessionIds === []) {
                    return;
                }

                if ($dryRun) {
                    $deletedSessions += count($sessionIds);

                    return;
                }

                // Closed Chats dashboard rows are live_chat_sessions.
                // Delete only sessions that are already ended and older than retention.
                $deletedSessions += LiveChatSession::query()
                    ->whereIn('id', $sessionIds)
                    ->whereNotNull('ended_at')
                    ->delete();
            });

        return [
            'conversations' => $deletedConversations,
            'sessions' => $deletedSessions,
            'attachments' => $deletedAttachments,
            'retention_days' => $retentionDays,
        ];
    }

    protected function cleanupChatbotConversations(bool $dryRun): array
    {
        $retentionDays = (int) config(
            'data-retention.chatbot_conversations.retention_days',
            30
        );
        $chunkSize = max(1, (int) config(
            'data-retention.chatbot_conversations.chunk_size',
            100
        ));
        $cutoff = now()->subDays($retentionDays);
        $deletedConversations = 0;
        $deletedAttachments = 0;

        $this->info("Chatbot conversation cutoff: {$cutoff->toDateTimeString()}");

        ChatConversation::query()
            ->select('id')
            // Normal chatbot conversations are ended by chatbot:end-inactive.
            // Resolved is also safe here. Keep active/live/waiting statuses out.
            ->whereIn('status', ['ended', 'resolved'])
            ->where(function ($query): void {
                $query
                    ->whereNull('mode')
                    ->orWhere('mode', 'ai');
            })
            ->whereRaw(
                'COALESCE(ended_at, updated_at, created_at) < ?',
                [$cutoff->toDateTimeString()]
            )
            ->orderBy('id')
            ->chunkById($chunkSize, function ($conversations) use ($dryRun, &$deletedConversations, &$deletedAttachments): void {
                $conversationIds = $conversations->pluck('id')->all();

                if ($conversationIds === []) {
                    return;
                }

                $attachmentPaths = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->whereNotNull('attachment')
                    ->pluck('attachment')
                    ->filter()
                    ->unique()
                    ->values();

                if ($dryRun) {
                    $deletedConversations += count($conversationIds);
                    $deletedAttachments += $attachmentPaths->count();

                    return;
                }

                foreach ($attachmentPaths as $path) {
                    if (Storage::disk('local')->delete($path)) {
                        $deletedAttachments++;
                    }
                }

                DB::transaction(function () use ($conversationIds, &$deletedConversations): void {
                    // chat_messages and chatbot_flow_answers cascade-delete through
                    // chat_conversations. We do not target leads or ratings directly.
                    $deletedConversations += ChatConversation::query()
                        ->whereIn('id', $conversationIds)
                        ->whereIn('status', ['ended', 'resolved'])
                        ->where(function ($query): void {
                            $query
                                ->whereNull('mode')
                                ->orWhere('mode', 'ai');
                        })
                        ->delete();
                });
            });

        return [
            'conversations' => $deletedConversations,
            'attachments' => $deletedAttachments,
            'retention_days' => $retentionDays,
        ];
    }

    protected function cleanupMissedChats(bool $dryRun): array
    {
        $retentionDays = (int) config('data-retention.missed_chats.retention_days', 30);
        $chunkSize = max(1, (int) config('data-retention.missed_chats.chunk_size', 100));
        $cutoff = now()->subDays($retentionDays);
        $deletedMissedChats = 0;

        $this->info("Missed chat cutoff: {$cutoff->toDateTimeString()}");

        ChatbotLead::query()
            ->select('id')
            ->where('source', 'live_chat_offline_request')
            // Missed Chats dashboard uses created_at for the 30-day window.
            // Delete only offline request leads older than that same retention window.
            ->where('created_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($leads) use ($dryRun, &$deletedMissedChats): void {
                $leadIds = $leads->pluck('id')->all();

                if ($leadIds === []) {
                    return;
                }

                if ($dryRun) {
                    $deletedMissedChats += count($leadIds);

                    return;
                }

                DB::transaction(function () use ($leadIds, &$deletedMissedChats): void {
                    // Delete only missed chat/offline request leads.
                    // Normal chatbot leads are handled by their own retention rules.
                    $deletedMissedChats += ChatbotLead::query()
                        ->whereIn('id', $leadIds)
                        ->where('source', 'live_chat_offline_request')
                        ->delete();
                });
            });

        return [
            'missed_chats' => $deletedMissedChats,
            'retention_days' => $retentionDays,
        ];
    }

    protected function cleanupVisitors(bool $dryRun): array
    {
        $retentionDays = (int) config('data-retention.visitors.retention_days', 30);
        $chunkSize = max(1, (int) config('data-retention.visitors.chunk_size', 100));
        $cutoff = now()->subDays($retentionDays);
        $deletedVisitors = 0;

        $this->info("Visitor cutoff: {$cutoff->toDateTimeString()}");

        Visitor::query()
            ->select('id')
            ->whereRaw(
                'COALESCE(last_activity_at, updated_at, created_at) < ?',
                [$cutoff->toDateTimeString()]
            )
            // Keep visitors that still have active, waiting, or live conversations.
            ->whereDoesntHave('conversations', function ($query): void {
                $query->whereIn('status', [
                    'active',
                    'waiting_customer',
                    'waiting_agent',
                    'live_active',
                ]);
            })
            ->orderBy('id')
            ->chunkById($chunkSize, function ($visitors) use ($dryRun, &$deletedVisitors): void {
                $visitorIds = $visitors->pluck('id')->all();

                if ($visitorIds === []) {
                    return;
                }

                if ($dryRun) {
                    $deletedVisitors += count($visitorIds);

                    return;
                }

                DB::transaction(function () use ($visitorIds, &$deletedVisitors): void {
                    // visitor_sessions cascade-delete with visitors.
                    // TODO: Keep this conservative; lead/rating records are not targeted directly.
                    $deletedVisitors += Visitor::query()
                        ->whereIn('id', $visitorIds)
                        ->whereDoesntHave('conversations', function ($query): void {
                            $query->whereIn('status', [
                                'active',
                                'waiting_customer',
                                'waiting_agent',
                                'live_active',
                            ]);
                        })
                        ->delete();
                });
            });

        return [
            'visitors' => $deletedVisitors,
            'retention_days' => $retentionDays,
        ];
    }
}
