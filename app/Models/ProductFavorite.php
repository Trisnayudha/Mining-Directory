<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductFavorite extends Model
{
    use  HasFactory;
    protected $table = 'products_favorite';
    protected $fillable = [
        'users_id',
        'product_id'
    ];
}
