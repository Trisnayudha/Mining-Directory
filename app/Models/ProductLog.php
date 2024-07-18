<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLog extends Model
{
    use HasFactory;

    protected $table = 'products_log';

    protected $fillable = [
        'product_id',
        'users_id'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
