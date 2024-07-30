<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use  HasFactory;
    protected $table = 'projects';

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
        'file',
        'views',
        'download',
        'category_media',
        'image',
        'location',
        'status'
    ];
    public function projectCategories()
    {
        return $this->hasMany(ProjectCategory::class, 'project_id', 'id');
    }
}
