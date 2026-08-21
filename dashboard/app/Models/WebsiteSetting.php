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
        'enable_live_chat',
        'show_connect_agent',

    ];
    protected $casts = [
    'position' => 'array',
    'temperature' => 'float',
    'enable_chatbot' => 'boolean',
    'enable_live_chat' => 'boolean',
    'show_connect_agent' => 'boolean',
];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}