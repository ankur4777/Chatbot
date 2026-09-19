<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotLead extends Model
{
    protected $fillable = [
        'website_id',
        'visitor_id',
        'conversation_id',
        'source',
        'name',
        'email',
        'phone',
        'notes',
        'assigned_agent_id',
        'assigned_by',
        'assigned_at',
        'followup_status',
        'agent_note',
        'last_contacted_at',
        'next_followup_at',
        'followup_reminder_enabled',
        'followup_reminder_at',
        'followup_reminder_sent_at',
        'resolved_at',
    ];

    public const FOLLOWUP_STATUSES = [
        'pending',
        'assigned',
        'contacted',
        'follow_up_required',
        'resolved',
        'unable_to_reach',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'last_contacted_at' => 'datetime',
            'next_followup_at' => 'datetime',
            'followup_reminder_enabled' => 'boolean',
            'followup_reminder_at' => 'datetime',
            'followup_reminder_sent_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public static function followupStatusLabels(): array
    {
        return [
            'pending' => 'Pending',
            'assigned' => 'Assigned',
            'contacted' => 'Contacted',
            'follow_up_required' => 'Follow-up Required',
            'resolved' => 'Resolved',
            'unable_to_reach' => 'Unable to Reach',
        ];
    }

    public function followupStatusLabel(): string
    {
        return self::followupStatusLabels()[$this->followup_status] ?? 'Pending';
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function visitor(): BelongsTo
    {
        return $this->belongsTo(Visitor::class);
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class);
    }

    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_agent_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
