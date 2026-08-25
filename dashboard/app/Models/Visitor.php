<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Visitor extends Model
{
    protected $fillable = [
        'website_id',
        'visitor_uuid',
        'ip_address',
        'first_seen_at',
        'last_activity_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(VisitorSession::class);
    }

    public function getTotalVisitsAttribute()
    {
        return $this->sessions()->count();
    }

}