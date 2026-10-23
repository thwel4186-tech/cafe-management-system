<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Order extends Model
{
    protected $fillable = ['order_number', 'user_id', 'cafe_table_id', 'type', 'status', 'total_amount', 'notes'];
    protected $casts = ['total_amount' => 'integer'];
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function table() { return $this->belongsTo(CafeTable::class, 'cafe_table_id'); }
    public function user() { return $this->belongsTo(User::class); }
}
