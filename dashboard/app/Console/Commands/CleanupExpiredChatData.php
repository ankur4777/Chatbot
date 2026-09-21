<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\ChatbotLead;
use App\Models\LiveChatClosure;
use App\Models\LiveChatRating;
use App\Models\LiveChatSession;
use App\Models\Visitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

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
            '%s expired data cleanup complete. Conversations deleted: %d, sessions deleted: %d, messages deleted: %d, attachments deleted: %d, visitors deleted: %d, closed counts preserved: %d, ratings preserved: %d, skipped due to preservation failure: %d.',
            $dryRun ? 'Dry-run' : 'Deleted',
            $closedChatStats['conversations'] + $chatbotStats['conversations'],
            $closedChatStats['sessions'],
            $closedChatStats['messages'] + $chatbotStats['messages'],
            $closedChatStats['attachments'] + $chatbotStats['attachments'],
            $visitorStats['visitors'],
            $closedChatStats['closures_preserved'],
            $closedChatStats['ratings_preserved'],
            $closedChatStats['skipped_rating_preservation_failures'],
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
        $deletedMessages = 0;
        $deletedAttachments = 0;
        $ratingsPreserved = 0;
        $closuresPreserved = 0;
        $skippedPreservationFailures = 0;

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
            ->chunkById($chunkSize, function ($conversations) use (
                $dryRun,
                &$deletedConversations,
                &$deletedMessages,
                &$deletedAttachments,
                &$ratingsPreserved,
                &$skippedPreservationFailures
            ): void {
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
                $messageCount = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->count();
                $sessionCount = LiveChatSession::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->count();

                if ($dryRun) {
                    $deletedConversations += count($conversationIds);
                    $deletedMessages += $messageCount;
                    $deletedSessions += $sessionCount;
                    $deletedAttachments += $attachmentPaths->count();

                    return;
                }

                $preservation = $this->preserveRatingsForConversations($conversationIds);
                $ratingsPreserved += $preservation['ratings_preserved'];
                $closuresPreserved += $preservation['closures_preserved'];
                $skippedPreservationFailures += $preservation['skipped'];
                $conversationIds = $preservation['safe_conversation_ids'];

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
                $messageCount = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->count();
                $sessionCount = LiveChatSession::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->count();

                foreach ($attachmentPaths as $path) {
                    if (Storage::disk('local')->delete($path)) {
                        $deletedAttachments++;
                    }
                }

                try {
                    DB::transaction(function () use (
                        $conversationIds,
                        $messageCount,
                        $sessionCount,
                        &$deletedConversations,
                        &$deletedMessages,
                        &$deletedSessions
                    ): void {
                        // chat_messages, chatbot_flow_answers, chatbot_leads, and live_chat_sessions
                        // are cascade-deleted by their foreign keys when the closed conversation is deleted.
                        $deletedConversations += ChatConversation::query()
                            ->whereIn('id', $conversationIds)
                            ->where('status', 'closed')
                            ->delete();
                        $deletedMessages += $messageCount;
                        $deletedSessions += $sessionCount;
                    });
                } catch (Throwable $exception) {
                    $skippedPreservationFailures += count($conversationIds);
                    Log::error('Skipped closed conversation cleanup after delete safety check failed.', [
                        'conversation_ids' => $conversationIds,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        LiveChatSession::query()
            ->select('id')
            ->whereNotNull('ended_at')
            ->where('ended_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById($chunkSize, function ($sessions) use (
                $dryRun,
                &$deletedSessions,
                &$ratingsPreserved,
                &$skippedPreservationFailures
            ): void {
                $sessionIds = $sessions->pluck('id')->all();

                if ($sessionIds === []) {
                    return;
                }

                if ($dryRun) {
                    $deletedSessions += count($sessionIds);

                    return;
                }

                $preservation = $this->preserveRatingsForSessions($sessionIds);
                $ratingsPreserved += $preservation['ratings_preserved'];
                $closuresPreserved += $preservation['closures_preserved'];
                $skippedPreservationFailures += $preservation['skipped'];
                $sessionIds = $preservation['safe_session_ids'];

                if ($sessionIds === []) {
                    return;
                }

                // Closed Chats dashboard rows are live_chat_sessions.
                // Delete only sessions that are already ended and older than retention.
                try {
                    $deletedSessions += LiveChatSession::query()
                        ->whereIn('id', $sessionIds)
                        ->whereNotNull('ended_at')
                        ->delete();
                } catch (Throwable $exception) {
                    $skippedPreservationFailures += count($sessionIds);
                    Log::error('Skipped live chat session cleanup after delete safety check failed.', [
                        'live_chat_session_ids' => $sessionIds,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        return [
            'conversations' => $deletedConversations,
            'sessions' => $deletedSessions,
            'messages' => $deletedMessages,
            'attachments' => $deletedAttachments,
            'ratings_preserved' => $ratingsPreserved,
            'closures_preserved' => $closuresPreserved,
            'skipped_rating_preservation_failures' => $skippedPreservationFailures,
            'retention_days' => $retentionDays,
        ];
    }

    protected function preserveRatingsForConversations(array $conversationIds): array
    {
        if ($conversationIds === []) {
            return [
                'safe_conversation_ids' => [],
                'ratings_preserved' => 0,
                'closures_preserved' => 0,
                'skipped' => 0,
            ];
        }

        $unsafeConversationIds = [];
        $ratingsPreserved = 0;
        $closuresPreserved = 0;

        LiveChatSession::query()
            ->with(['agent', 'conversation.website', 'conversation.visitor'])
            ->whereIn('conversation_id', $conversationIds)
            ->whereNotNull('ended_at')
            ->orderBy('id')
            ->each(function (LiveChatSession $session) use (&$unsafeConversationIds, &$closuresPreserved): void {
                try {
                    $closure = LiveChatClosure::preserveFromSession($session);

                    if (! $closure) {
                        $unsafeConversationIds[] = $session->conversation_id;
                        Log::error('Skipped conversation cleanup because closed chat count was not preserved.', [
                            'conversation_id' => $session->conversation_id,
                            'live_chat_session_id' => $session->id,
                        ]);

                        return;
                    }

                    $closuresPreserved++;
                } catch (Throwable $exception) {
                    $unsafeConversationIds[] = $session->conversation_id;
                    Log::error('Skipped conversation cleanup because closed chat count preservation failed.', [
                        'conversation_id' => $session->conversation_id,
                        'live_chat_session_id' => $session->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        LiveChatSession::query()
            ->with(['agent', 'conversation.website', 'conversation.visitor'])
            ->whereIn('conversation_id', $conversationIds)
            ->where('rating_status', 'submitted')
            ->whereNotNull('rating')
            ->orderBy('id')
            ->each(function (LiveChatSession $session) use (&$unsafeConversationIds, &$ratingsPreserved): void {
                try {
                    $rating = LiveChatRating::preserveFromSession($session);

                    if (! $rating) {
                        $unsafeConversationIds[] = $session->conversation_id;
                        Log::error('Skipped conversation cleanup because submitted rating was not preserved.', [
                            'conversation_id' => $session->conversation_id,
                            'live_chat_session_id' => $session->id,
                        ]);

                        return;
                    }

                    $ratingsPreserved++;
                } catch (Throwable $exception) {
                    $unsafeConversationIds[] = $session->conversation_id;
                    Log::error('Skipped conversation cleanup because rating preservation failed.', [
                        'conversation_id' => $session->conversation_id,
                        'live_chat_session_id' => $session->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        $unsafeConversationIds = array_values(array_unique(array_filter($unsafeConversationIds)));

        return [
            'safe_conversation_ids' => array_values(array_diff($conversationIds, $unsafeConversationIds)),
            'ratings_preserved' => $ratingsPreserved,
            'closures_preserved' => $closuresPreserved,
            'skipped' => count($unsafeConversationIds),
        ];
    }

    protected function preserveRatingsForSessions(array $sessionIds): array
    {
        if ($sessionIds === []) {
            return [
                'safe_session_ids' => [],
                'ratings_preserved' => 0,
                'closures_preserved' => 0,
                'skipped' => 0,
            ];
        }

        $unsafeSessionIds = [];
        $ratingsPreserved = 0;
        $closuresPreserved = 0;

        LiveChatSession::query()
            ->with(['agent', 'conversation.website', 'conversation.visitor'])
            ->whereIn('id', $sessionIds)
            ->whereNotNull('ended_at')
            ->orderBy('id')
            ->each(function (LiveChatSession $session) use (&$unsafeSessionIds, &$closuresPreserved): void {
                try {
                    $closure = LiveChatClosure::preserveFromSession($session);

                    if (! $closure) {
                        $unsafeSessionIds[] = $session->id;
                        Log::error('Skipped live chat session cleanup because closed chat count was not preserved.', [
                            'conversation_id' => $session->conversation_id,
                            'live_chat_session_id' => $session->id,
                        ]);

                        return;
                    }

                    $closuresPreserved++;
                } catch (Throwable $exception) {
                    $unsafeSessionIds[] = $session->id;
                    Log::error('Skipped live chat session cleanup because closed chat count preservation failed.', [
                        'conversation_id' => $session->conversation_id,
                        'live_chat_session_id' => $session->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        LiveChatSession::query()
            ->with(['agent', 'conversation.website', 'conversation.visitor'])
            ->whereIn('id', $sessionIds)
            ->where('rating_status', 'submitted')
            ->whereNotNull('rating')
            ->orderBy('id')
            ->each(function (LiveChatSession $session) use (&$unsafeSessionIds, &$ratingsPreserved): void {
                try {
                    $rating = LiveChatRating::preserveFromSession($session);

                    if (! $rating) {
                        $unsafeSessionIds[] = $session->id;
                        Log::error('Skipped live chat session cleanup because submitted rating was not preserved.', [
                            'conversation_id' => $session->conversation_id,
                            'live_chat_session_id' => $session->id,
                        ]);

                        return;
                    }

                    $ratingsPreserved++;
                } catch (Throwable $exception) {
                    $unsafeSessionIds[] = $session->id;
                    Log::error('Skipped live chat session cleanup because rating preservation failed.', [
                        'conversation_id' => $session->conversation_id,
                        'live_chat_session_id' => $session->id,
                        'error' => $exception->getMessage(),
                    ]);
                }
            });

        $unsafeSessionIds = array_values(array_unique($unsafeSessionIds));

        return [
            'safe_session_ids' => array_values(array_diff($sessionIds, $unsafeSessionIds)),
            'ratings_preserved' => $ratingsPreserved,
            'closures_preserved' => $closuresPreserved,
            'skipped' => count($unsafeSessionIds),
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
        $deletedMessages = 0;
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
            ->chunkById($chunkSize, function ($conversations) use ($dryRun, &$deletedConversations, &$deletedMessages, &$deletedAttachments): void {
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
                $messageCount = ChatMessage::query()
                    ->whereIn('conversation_id', $conversationIds)
                    ->count();

                if ($dryRun) {
                    $deletedConversations += count($conversationIds);
                    $deletedMessages += $messageCount;
                    $deletedAttachments += $attachmentPaths->count();

                    return;
                }

                foreach ($attachmentPaths as $path) {
                    if (Storage::disk('local')->delete($path)) {
                        $deletedAttachments++;
                    }
                }

                DB::transaction(function () use ($conversationIds, $messageCount, &$deletedConversations, &$deletedMessages): void {
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
                    $deletedMessages += $messageCount;
                });
            });

        return [
            'conversations' => $deletedConversations,
            'messages' => $deletedMessages,
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
