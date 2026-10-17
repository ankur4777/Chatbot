<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class WebsiteGalleryImage extends Model
{
    protected $fillable = [
        'company_id',
        'website_id',
        'category',
        'title',
        'description',
        'tags',
        'image_path',
        'mime_type',
        'size',
        'is_active',
    ];

    protected $casts = [
        'tags' => 'array',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (WebsiteGalleryImage $image): void {
            if (! $image->company_id && $image->website_id) {
                $image->company_id = $image->website?->company_id;
            }

            if ($image->image_path) {
                $disk = Storage::disk(config('gallery.storage_disk', 'public'));
                $image->mime_type = $disk->mimeType($image->image_path) ?: $image->mime_type ?: 'application/octet-stream';
                $image->size = $disk->size($image->image_path) ?: $image->size ?: 0;
            }

            if ($image->isDirty('image_path') && $image->getOriginal('image_path')) {
                Storage::disk(config('gallery.storage_disk', 'public'))
                    ->delete($image->getOriginal('image_path'));
            }
        });

        static::deleted(function (WebsiteGalleryImage $image): void {
            if ($image->image_path) {
                Storage::disk(config('gallery.storage_disk', 'public'))
                    ->delete($image->image_path);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(Website::class);
    }

    public function fileExists(): bool
    {
        return $this->image_path
            && Storage::disk(config('gallery.storage_disk', 'public'))
                ->exists($this->image_path);
    }
}
