<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'price', 'cost', 'stock', 'track_stock', 'is_active'];
    protected $casts = ['track_stock' => 'boolean', 'is_active' => 'boolean', 'price' => 'integer', 'cost' => 'integer', 'stock' => 'integer'];
    public function category() { return $this->belongsTo(Category::class); }
    public function orderItems() { return $this->hasMany(OrderItem::class); }
}
