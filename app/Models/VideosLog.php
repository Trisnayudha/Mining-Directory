<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VideosLog extends Model
{
    use HasFactory;

    protected $table = 'videos_log';

    protected $fillable = [
        'video_id',
        'users_id'
    ];

    public function video()
    {
        return $this->belongsTo(Videos::class);
    }
}
