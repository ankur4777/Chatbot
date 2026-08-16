<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotFlowOption extends Model
{
    protected $fillable = [
        'chatbot_flow_step_id',
        'label',
        'value',
        'sort_order',
    ];

    public function step(): BelongsTo
    {
        return $this->belongsTo(ChatbotFlowStep::class, 'chatbot_flow_step_id');
    }
}