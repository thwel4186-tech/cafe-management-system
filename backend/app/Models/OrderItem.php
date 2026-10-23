<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class OrderItem extends Model
{
    public $timestamps = false;
    protected $fillable = ['order_id', 'product_id', 'product_name', 'qty', 'unit_price', 'unit_cost', 'subtotal'];
    protected $casts = ['qty' => 'integer', 'unit_price' => 'integer', 'unit_cost' => 'integer', 'subtotal' => 'integer'];
    public function product() { return $this->belongsTo(Product::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
