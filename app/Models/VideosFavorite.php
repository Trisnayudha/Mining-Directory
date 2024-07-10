<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosFavorite extends Model
{
    use  HasFactory;
    protected $table = 'videos_favorite';
    protected $fillable = [
        'users_id',
        'video_id'
    ];
}
