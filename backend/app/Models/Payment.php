<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $fillable = ['order_id', 'user_id', 'method', 'amount', 'paid_at'];
    protected $casts = ['amount' => 'integer', 'paid_at' => 'datetime'];
    public function order() { return $this->belongsTo(Order::class); }
}
