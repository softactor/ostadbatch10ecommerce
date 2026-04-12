<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = ['order_id', 'invoice_number', 'status', 'pdf_path'];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            $order->invoice_number = 'INV-' . strtoupper(uniqid());
        });
    }
    
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}