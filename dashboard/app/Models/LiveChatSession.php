<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatSession extends Model
{
    public const AGENT_CHAT_STATUS_ACTIVE = 'active';
    public const AGENT_CHAT_STATUS_ON_HOLD = 'on_hold';
    public const AGENT_CHAT_STATUS_AWAITING_VISITOR = 'awaiting_visitor';

    protected $fillable = [
        'conversation_id',
        'agent_id',
        'started_at',
        'ended_at',
        'ended_by',
        'note',
        'note_updated_at',
        'rating_status',
        'rating',
        'feedback',
        'submitted_at',
        'skipped_at',
        'agent_chat_status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'note_updated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'skipped_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (LiveChatSession $session): void {
            if ($session->ended_at && ! LiveChatClosure::preserveFromSession($session)) {
                throw new \RuntimeException(
                    "Cannot delete live chat session {$session->id}; closed chat count was not preserved."
                );
            }

            if (
                $session->rating_status === 'submitted'
                && $session->rating
                && ! LiveChatRating::preserveFromSession($session)
            ) {
                throw new \RuntimeException(
                    "Cannot delete live chat session {$session->id}; submitted rating was not preserved."
                );
            }
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public static function agentChatStatuses(): array
    {
        return [
            self::AGENT_CHAT_STATUS_ACTIVE,
            self::AGENT_CHAT_STATUS_ON_HOLD,
            self::AGENT_CHAT_STATUS_AWAITING_VISITOR,
        ];
    }

    public static function agentChatStatusLabels(): array
    {
        return [
            self::AGENT_CHAT_STATUS_ACTIVE => 'Active',
            self::AGENT_CHAT_STATUS_ON_HOLD => 'On Hold',
            self::AGENT_CHAT_STATUS_AWAITING_VISITOR => 'Awaiting Response',
        ];
    }

    public function agentChatStatusLabel(): string
    {
        return self::agentChatStatusLabels()[
            $this->agent_chat_status ?: self::AGENT_CHAT_STATUS_ACTIVE
        ] ?? 'Active';
    }
}
