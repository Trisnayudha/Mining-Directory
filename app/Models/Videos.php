<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Videos extends Model
{
    use  HasFactory;
    protected $table = 'videos';

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
        'asset',
        'views',
        'status'

    ];
    public function videoCategories()
    {
        return $this->hasMany(VideosCategory::class, 'videos_id', 'id');
    }
}
