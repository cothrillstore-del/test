<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand_id',
        'category_id',
        'name',
        'slug',
        'model_year',
        'sku',
        'description',
        'short_description',
        'specifications',
        'features',
        'price_min',
        'price_max',
        'retail_price',
        'release_date',
        'status',
        'is_featured',
        'main_image',
        'gallery_images',
        'view_count',
        'average_rating',
        'review_count',
        'meta_title',
        'meta_description',
        'meta_keywords'
    ];

    protected $casts = [
        'specifications' => 'array',
        'features' => 'array',
        'gallery_images' => 'array',
        'is_featured' => 'boolean',
        'release_date' => 'date',
        'price_min' => 'decimal:2',
        'price_max' => 'decimal:2',
        'retail_price' => 'decimal:2',
        'average_rating' => 'decimal:2',
    ];

    // Relationships
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Automatically generate slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name . ' ' . $product->model_year);
            }
            if (empty($product->sku)) {
                $product->sku = strtoupper(Str::random(3) . '-' . time());
            }
        });
    }

    // Accessors
    public function getPriceRangeAttribute()
    {
        if ($this->price_min && $this->price_max) {
            if ($this->price_min == $this->price_max) {
                return '$' . number_format($this->price_min, 2);
            }
            return '$' . number_format($this->price_min, 2) . ' - $' . number_format($this->price_max, 2);
        }
        return 'Price not available';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeWithFilters($query, $filters)
    {
        return $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        })->when($filters['brand_id'] ?? null, function ($query, $brandId) {
            $query->where('brand_id', $brandId);
        })->when($filters['category_id'] ?? null, function ($query, $categoryId) {
            $query->where('category_id', $categoryId);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->when($filters['is_featured'] ?? null, function ($query, $featured) {
            $query->where('is_featured', $featured);
        })->when($filters['year'] ?? null, function ($query, $year) {
            $query->where('model_year', $year);
        });
    }

    // Methods
    public function incrementViewCount()
    {
        $this->increment('view_count');
    }

    public function updateRating()
    {
        $this->update([
            'average_rating' => $this->reviews()->avg('rating') ?? 0,
            'review_count' => $this->reviews()->count()
        ]);
    }
}