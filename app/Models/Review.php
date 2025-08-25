<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'title',
        'content',
        'rating',
        'pros',
        'cons',
        'verified_purchase',
        'helpful_count',
        'unhelpful_count',
        'test_conditions',
        'skill_level',
        'status',
        'rejection_reason',
        'approved_by',
        'approved_at',
        'is_featured'
    ];

    protected $casts = [
        'pros' => 'array',
        'cons' => 'array',
        'test_conditions' => 'array',
        'verified_purchase' => 'boolean',
        'is_featured' => 'boolean',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function media()
    {
        return $this->hasMany(ReviewMedia::class);
    }

    public function comments()
    {
        return $this->hasMany(ReviewComment::class);
    }

    public function votes()
    {
        return $this->hasMany(ReviewVote::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeFlagged($query)
    {
        return $query->where('status', 'flagged');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeVerifiedPurchase($query)
    {
        return $query->where('verified_purchase', true);
    }

    public function scopeWithFilters($query, $filters)
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        })->when($filters['product_id'] ?? null, function ($query, $productId) {
            $query->where('product_id', $productId);
        })->when($filters['user_id'] ?? null, function ($query, $userId) {
            $query->where('user_id', $userId);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['rating'] ?? null, function ($query, $rating) {
            $query->where('rating', $rating);
        })->when($filters['verified_purchase'] ?? null, function ($query, $verified) {
            $query->where('verified_purchase', $verified);
        })->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
            $query->whereDate('created_at', '>=', $dateFrom);
        })->when($filters['date_to'] ?? null, function ($query, $dateTo) {
            $query->whereDate('created_at', '<=', $dateTo);
        });
    }

    // Methods
    public function approve($userId = null)
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null
        ]);

        // Update product rating
        $this->product->updateRating();
    }

    public function reject($reason = null, $userId = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $userId ?? auth()->id(),
            'approved_at' => now()
        ]);
    }

    public function flag($reason = null)
    {
        $this->update([
            'status' => 'flagged',
            'rejection_reason' => $reason
        ]);
    }

    public function incrementHelpful()
    {
        $this->increment('helpful_count');
    }

    public function incrementUnhelpful()
    {
        $this->increment('unhelpful_count');
    }

    public function getHelpfulnessPercentage()
    {
        $total = $this->helpful_count + $this->unhelpful_count;
        if ($total === 0) return 0;
        return round(($this->helpful_count / $total) * 100);
    }
}