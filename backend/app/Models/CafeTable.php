<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CafeTable extends Model
{
    protected $fillable = ['name', 'seats'];
    public function orders() { return $this->hasMany(Order::class, 'cafe_table_id'); }
}
