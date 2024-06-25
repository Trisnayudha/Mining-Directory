<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MdCategoryCompany extends Model
{
    use  HasFactory;
    protected $table = 'md_category_company';

    public function subCategories()
    {
        return $this->hasMany(MdSubCategoryCompany::class, 'category_id', 'id');
    }

    // Relasi ke tabel popular
    public function popular()
    {
        return $this->hasOne(MdCategoryPopular::class, 'category_id', 'id');
    }
}
