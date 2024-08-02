<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaResource extends Model
{
    use  HasFactory;
    protected $table = 'media_resource';

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
        'category_media',
        'image',
        'status'

    ];

    public function mediaCategories()
    {
        return $this->hasMany(MediaCategory::class, 'media_resource_id', 'id');
    }
}
