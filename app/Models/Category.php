<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $table = "categories";
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];
}
