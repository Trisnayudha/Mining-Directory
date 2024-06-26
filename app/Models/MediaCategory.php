<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaCategory extends Model
{
    use  HasFactory;
    protected $table = 'media_resource_category_list';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'media_resource_id', 'category_id'
    ];
    public function mdCategory()
    {
        return $this->belongsTo(MdCategoryCompany::class, 'category_id', 'id');
    }
}
