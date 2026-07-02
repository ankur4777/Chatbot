<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Website extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'domain',
        'status',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function settings(): HasOne
    {
        return $this->hasOne(WebsiteSetting::class);
    }

    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(KnowledgeCategory::class);      
    }

    public function visitors(): HasMany
    {
    return $this->hasMany(Visitor::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(ChatConversation::class);
    }

    public function leads(): HasMany
    {
        return $this->hasMany(ChatbotLead::class);
    }

    public function quickReplies(): HasMany
{
    return $this->hasMany(ChatbotQuickReply::class);
}
}