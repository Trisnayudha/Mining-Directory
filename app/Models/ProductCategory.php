<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use  HasFactory;
    protected $table = 'products_category_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'product_id', 'category_id'
    ];
    public function mdCategory()
    {
        return $this->belongsTo(MdCategoryCompany::class, 'category_id', 'id');
    }
}
