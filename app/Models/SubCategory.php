<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = 'sub_categories';
    protected $fillable = [
        'id',
        'name',
        'slug',
        'status',
        'category_id',
    ];
}
