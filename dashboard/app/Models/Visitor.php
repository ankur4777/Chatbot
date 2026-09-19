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
        'name',
        'email',
        'phone',
        'ip_address',
        'first_seen_at',
        'last_activity_at',
        'details_updated_by',
        'details_updated_at',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'details_updated_at' => 'datetime',
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

    public function detailsUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'details_updated_by');
    }

    public function displayName(): string
    {
        if (filled($this->name)) {
            return $this->name;
        }

        return $this->visitor_uuid
            ? 'Visitor ' . substr($this->visitor_uuid, 0, 8)
            : 'Unknown Visitor';
    }

    public function initials(): string
    {
        if (filled($this->name)) {
            return collect(explode(' ', trim($this->name)))
                ->filter()
                ->map(fn (string $part) => substr($part, 0, 1))
                ->take(2)
                ->implode('');
        }

        $uuid = $this->visitor_uuid ?: 'unknown';

        return strtoupper(substr($uuid, 0, 1) . substr($uuid, -1));
    }

    public function getTotalVisitsAttribute()
    {
        return $this->sessions()->count();
    }

}
