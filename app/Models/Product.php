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

}
