<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewMedia extends Model
{
    use HasFactory;

    protected $table = 'review_media';

    protected $fillable = [
        'review_id',
        'media_type',
        'media_url',
        'thumbnail_url',
        'caption',
        'sort_order'
    ];

    // Relationships
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    // Scopes
    public function scopeImages($query)
    {
        return $query->where('media_type', 'image');
    }

    public function scopeVideos($query)
    {
        return $query->where('media_type', 'video');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }
}