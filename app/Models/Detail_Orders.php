<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Detail_Orders extends Model
{
    use HasFactory;

    protected $table = 'detail_orders'; 
    
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];


public function product()
{
    return $this->belongsTo(Product::class, 'product_id');
}
public function order()
{
    return $this->belongsTo(Orders::class, 'order_id');
}
}
