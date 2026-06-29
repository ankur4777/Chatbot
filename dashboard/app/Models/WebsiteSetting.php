<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebsiteSetting extends Model
{
    protected $fillable = [

        'website_id',

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

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}