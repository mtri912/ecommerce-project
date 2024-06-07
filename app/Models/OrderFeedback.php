<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderFeedback extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'feedback'];

    protected $table = 'order_feedbacks';
    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
