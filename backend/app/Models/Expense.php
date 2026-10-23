<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Expense extends Model
{
    protected $fillable = ['user_id', 'title', 'amount', 'spent_on', 'notes'];
    protected $casts = ['amount' => 'integer', 'spent_on' => 'date:Y-m-d'];
}
