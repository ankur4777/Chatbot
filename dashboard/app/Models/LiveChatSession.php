<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatSession extends Model
{
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
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'note_updated_at' => 'datetime',
        'submitted_at' => 'datetime',
        'skipped_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
