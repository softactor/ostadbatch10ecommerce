<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'price', 'discount_price', 'stock', 'brand_id', 'category_id', 'is_featured', 'view_count', 'images', 'is_active'];
    
    // Scopes
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    public function scopePopular($query)
    {
        return $query->orderBy('view_count', 'desc');
    }
    
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }
    
    // Relations
    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    
    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function wishlistedBy()
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }


    public function reviews()
    {
        return $this->hasMany(Review::class)->where('is_approved', true);
    }
    
    public function allReviews()
    {
        return $this->hasMany(Review::class);
    }
    
    // এভারেজ রেটিং ক্যালকুলেশন
    public function getAverageRatingAttribute()
    {
        return round($this->reviews()->avg('rating'), 1);
    }
    
    // রেটিং ডিস্ট্রিবিউশন (১-৫ তারকার সংখ্যা)
    public function getRatingDistributionAttribute()
    {
        // $distribution = [];
        // for ($i = 1; $i <= 5; $i++) {
        //     $distribution[$i] = $this->reviews()->where('rating', $i)->count();
        // }
        // return $distribution;

        return $this->reviews()
            ->selectRaw('rating, count(*) as total')
            ->groupBy('rating')
            ->pluck('total', 'rating')
            ->all();


    }
    
    // রিভিউ কাউন্ট
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }


}
