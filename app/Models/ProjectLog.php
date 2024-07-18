<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectLog extends Model
{
    use HasFactory;

    protected $table = 'projects_log';

    protected $fillable = [
        'project_id',
        'users_id'
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
