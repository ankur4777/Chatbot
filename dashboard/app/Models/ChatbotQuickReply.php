<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatbotQuickReply extends Model
{
    protected $fillable = [
        'website_id',
        'label',
        'value',
        'icon',
        'sort_order',
        'is_active',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
}