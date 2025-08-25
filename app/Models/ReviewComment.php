<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'parent_comment_id',
        'content',
        'is_edited',
        'status'
    ];

    protected $casts = [
        'is_edited' => 'boolean',
    ];

    // Relationships
    public function review()
    {
        return $this->belongsTo(Review::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ReviewComment::class, 'parent_comment_id');
    }

    public function replies()
    {
        return $this->hasMany(ReviewComment::class, 'parent_comment_id');
    }

    // Scopes
    public function scopeVisible($query)
    {
        return $query->where('status', 'visible');
    }

    public function scopeRootComments($query)
    {
        return $query->whereNull('parent_comment_id');
    }
}