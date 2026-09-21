<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChatConversation extends Model
{
    protected $fillable = [
        'website_id',
        'visitor_id',
        'assigned_agent_id',
        'status',
        'mode',
        'started_at',
        'ended_at',
        'summary',
        'lead_step',
        'lead_completed',
        'handoff_requested_at',
        'assigned_at',
        'live_started_at',
        'live_ended_at',
        'closed_by_id',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'lead_completed' => 'boolean',
        'handoff_requested_at' => 'datetime',
        'assigned_at' => 'datetime',
        'live_started_at' => 'datetime',
        'live_ended_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (ChatConversation $conversation): void {
            $conversation
                ->liveChatSessions()
                ->with(['agent', 'conversation.website', 'conversation.visitor'])
                ->whereNotNull('ended_at')
                ->each(function (LiveChatSession $session) use ($conversation): void {
                    if (! LiveChatClosure::preserveFromSession($session)) {
                        throw new \RuntimeException(
                            "Cannot delete conversation {$conversation->id}; closed chat count for session {$session->id} was not preserved."
                        );
                    }
                });

            $conversation
                ->liveChatSessions()
                ->with(['agent', 'conversation.website', 'conversation.visitor'])
                ->where('rating_status', 'submitted')
                ->whereNotNull('rating')
                ->each(function (LiveChatSession $session) use ($conversation): void {
                    if (! LiveChatRating::preserveFromSession($session)) {
                        throw new \RuntimeException(
                            "Cannot delete conversation {$conversation->id}; submitted rating for session {$session->id} was not preserved."
                        );
                    }
                });
        });
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class, 'conversation_id');
    }

    public function liveChatSessions(): HasMany
    {
        return $this->hasMany(LiveChatSession::class, 'conversation_id');
    }

    public function activeLiveChatSession(): HasOne
    {
        return $this->hasOne(LiveChatSession::class, 'conversation_id')
            ->whereNull('ended_at')
            ->latestOfMany();
    }

    public function lead()
    {
        return $this->hasOne(ChatbotLead::class, 'conversation_id');
    }
    public function flowAnswers(): HasMany
{
    return $this->hasMany(ChatbotFlowAnswer::class, 'conversation_id');
}

public function isAiActive(): bool
{
    return $this->status === 'active'
        && ($this->mode ?? 'ai') === 'ai';
}

public function isWaitingForAgent(): bool
{
    return $this->status === 'waiting_agent';
}

public function isLiveActive(): bool
{
    return $this->status === 'live_active'
        && $this->mode === 'live';
}
}
