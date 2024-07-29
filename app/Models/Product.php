<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use  HasFactory;
    protected $table = 'products';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'title',
        'slug',
        'description',
        'file',
        'views',
        'download',
        'category_product',
        'location',
        'status'

    ];
    // Relasi One-to-Many dengan ProductsAsset
    public function products_asset()
    {
        return $this->hasMany(ProductAsset::class, 'product_id', 'id');
    }
    public function productCategories()
    {
        return $this->hasMany(ProductCategory::class, 'product_id', 'id');
    }
}
