<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaResourceLog extends Model
{
    use HasFactory;

    protected $table = 'media_resource_log';

    protected $fillable = [
        'media_resource_id',
        'users_id'
    ];

    public function mediaResource()
    {
        return $this->belongsTo(MediaResource::class);
    }
}
