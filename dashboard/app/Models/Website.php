<?php

namespace App\Models;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Visitor;

class Website extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'widget_key',
        'domain',
        'status',
    ];

    protected static function booted(): void
{
    static::creating(function ($website) {

        if (empty($website->widget_key)) {

            do {
                $key = 'WGT_' . Str::upper(Str::random(16));
            } while (
                self::where('widget_key', $key)->exists()
            );

            $website->widget_key = $key;
        }
    });
}

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

    public function knowledgeSources(): HasMany
{
    return $this->hasMany(KnowledgeSource::class);
}

public function chatbotLeads()
{
    return $this->hasMany(\App\Models\ChatbotLead::class, 'website_id');
}
public function chatbotFlow(): HasOne
{
    return $this->hasOne(ChatbotFlow::class);
}
}