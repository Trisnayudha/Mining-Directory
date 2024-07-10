<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaResourceFavorite extends Model
{
    use  HasFactory;
    protected $table = 'media_resource_favorite';
    protected $fillable = [
        'users_id',
        'media_resource_id'
    ];
}
