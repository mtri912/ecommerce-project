<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    public function items() {
        return $this->hasMany(OrderItem::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(OrderFeedback::class);
    }
}
