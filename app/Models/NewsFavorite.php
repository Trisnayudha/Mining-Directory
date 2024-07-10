<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsFavorite extends Model
{
    use  HasFactory;
    protected $table = 'news_favorite';
    protected $fillable = [
        'users_id',
        'news_id'
    ];
}
