<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeSource extends Model
{
    protected $fillable = [
        'website_id',
        'type',
        'title',
        'source',
        'status',
        'pages',
        'chunks',
        'error',
        'last_synced_at',
        'knowledge_category_id',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }
    public function knowledgeCategory(): BelongsTo
{
    return $this->belongsTo(KnowledgeCategory::class);
}

public function knowledgeBase(): HasOne
{
    return $this->hasOne(KnowledgeBase::class);
}
public function websiteKnowledgeSources(): HasMany
{
    return $this->hasMany(
        KnowledgeSource::class,
        'website_id',
        'website_id'
    );
}

}