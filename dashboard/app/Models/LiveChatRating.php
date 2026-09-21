<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatRating extends Model
{
    protected $fillable = [
        'live_chat_session_id',
        'conversation_id',
        'agent_id',
        'company_id',
        'website_id',
        'visitor_id',
        'rating',
        'feedback',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(LiveChatSession::class, 'live_chat_session_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public static function preserveFromSession(LiveChatSession $session): ?self
    {
        if ($session->rating_status !== 'submitted' || ! $session->rating || ! $session->agent_id) {
            return null;
        }

        $session->loadMissing(['agent', 'conversation.website', 'conversation.visitor']);

        $companyId = $session->agent?->company_id
            ?? $session->conversation?->website?->company_id;

        if (! $companyId) {
            return null;
        }

        return self::updateOrCreate(
            ['live_chat_session_id' => $session->id],
            [
                'conversation_id' => $session->conversation_id,
                'agent_id' => $session->agent_id,
                'company_id' => $companyId,
                'website_id' => $session->conversation?->website_id,
                'visitor_id' => $session->conversation?->visitor_id,
                'rating' => $session->rating,
                'feedback' => $session->feedback,
                'submitted_at' => $session->submitted_at ?? $session->updated_at ?? now(),
            ]
        );
    }
}
