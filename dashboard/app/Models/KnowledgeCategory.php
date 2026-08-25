<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KnowledgeCategory extends Model
{
    protected $fillable = [
        'website_id',
        'name',
        'description',
    ];

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function knowledgeBases(): HasMany
    {
        return $this->hasMany(KnowledgeBase::class);
    }
    public function knowledgeSources()
{
    return $this->hasMany(
        \App\Models\KnowledgeSource::class,
        'knowledge_category_id'
    );
}

public function websiteCategories(): HasMany
{
    return $this->hasMany(
        KnowledgeCategory::class,
        'website_id',
        'website_id'
    );
}
}