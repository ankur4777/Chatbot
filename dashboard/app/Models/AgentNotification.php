<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AgentNotification extends Model
{
    public const TYPE_NEW_VISITOR_MESSAGE = 'new_visitor_message';
    public const TYPE_VISITOR_REPLIED = 'visitor_replied';
    public const TYPE_FOLLOW_UP_REMINDER = 'follow_up_reminder';
    public const TYPE_MISSED_CHAT_ASSIGNED = 'missed_chat_assigned';
    public const TYPE_CONVERSATION_CLOSED = 'conversation_closed';
    public const TYPE_SYSTEM = 'system';

    protected $fillable = [
        'agent_id',
        'company_id',
        'website_id',
        'conversation_id',
        'missed_chat_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }

    public function missedChat(): BelongsTo
    {
        return $this->belongsTo(ChatbotLead::class, 'missed_chat_id');
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->where('is_read', false);
    }
}
