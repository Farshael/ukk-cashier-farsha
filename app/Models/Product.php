<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'image',
        'price',
        'stock',
    ];

    public function orders()
{
    return $this->belongsToMany(Orders::class, 'detail_orders')
    ->withPivot('quantity', 'unit_price', 'subtotal')
    ->withTimestamps();
}
    public function orderDetails()
{
    return $this->hasMany(Detail_Orders::class, 'order_id');

}

}
