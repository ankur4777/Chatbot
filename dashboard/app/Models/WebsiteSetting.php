<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSetting extends Model
{
    protected $fillable = [

        'website_id',
        'system_prompt',
        'chatbot_name',
        'welcome_message',
        'placeholder',
        'language',

        'model',
        'temperature',

        'primary_color',
        'position',

        'enable_chatbot',
        'enable_ai_responses',
        'enable_live_chat',
        'offline_behavior',
        'max_active_chats_per_agent',
        'offline_message',
        'waiting_message',

    ];

    protected $casts = [
        'position' => 'array',
        'temperature' => 'float',
        'enable_chatbot' => 'boolean',
        'enable_ai_responses' => 'boolean',
        'enable_live_chat' => 'boolean',
        'max_active_chats_per_agent' => 'integer',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}
