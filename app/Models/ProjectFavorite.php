<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectFavorite extends Model
{
    use  HasFactory;
    protected $table = 'projects_favorite';
    protected $fillable = [
        'users_id',
        'project_id'
    ];
}
