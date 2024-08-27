<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectProduct extends Model
{
    use  HasFactory;
    protected $table = 'projects_product_list';
    protected $fillable = [
        'product_id',
        'project_id'
    ];
}
