<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'total_price',
        'discount',
        'final_price',
        'amount_paid',
        'change',
        'user_id',
        'points_used',
    ];

    public function product()
{
    return $this->belongsToMany(Product::class, 'Detail_Orders')
    ->withPivot('quantity', 'unit_price', 'subtotal')
    ->withTimestamps();
}


public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function user()
{
    return $this->belongsTo(User::class);
}

public function orderDetails()
{
    return $this->hasMany(Detail_Orders::class, 'order_id');
}

}
