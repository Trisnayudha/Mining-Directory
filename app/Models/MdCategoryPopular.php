<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MdCategoryPopular extends Model
{
    use  HasFactory;
    protected $table = 'md_category_popular';

    public function category()
    {
        return $this->belongsTo(MdCategoryCompany::class, 'category_id', 'id');
    }
}
