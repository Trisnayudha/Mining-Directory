<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use  HasFactory;
    protected $table = 'news';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'views',
        'date_news',
        'title',
        'slug',
        'sub_title',
        'description',
        'image',
        'status'

    ];
    public function newsCategories()
    {
        return $this->hasMany(NewsCategory::class, 'news_id', 'id');
    }
}
