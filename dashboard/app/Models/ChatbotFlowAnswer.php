<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFlowAnswer extends Model
{
    protected $fillable = [
        'conversation_id',
        'chatbot_flow_step_id',
        'answer',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(ChatConversation::class, 'conversation_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ChatbotFlowStep::class, 'chatbot_flow_step_id');
    }
}