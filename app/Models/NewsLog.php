<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsLog extends Model
{
    use HasFactory;

    protected $table = 'news_log';

    protected $fillable = [
        'news_id',
        'users_id'
    ];

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
