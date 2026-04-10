<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['user_id', 'product_id', 'rating', 'title', 'comment', 'is_approved', 'admin_reply'];
    
    protected $casts = [
        'is_approved' => 'boolean'
    ];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    // পেন্ডিং রিভিউ
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }
    
    // অ্যাপ্রুভড রিভিউ
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

}
