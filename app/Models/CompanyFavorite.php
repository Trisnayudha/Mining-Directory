<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyFavorite extends Model
{
    use  HasFactory;
    protected $table = 'company_users_favorite';
    protected $fillable = [
        'users_id',
        'company_id'
    ];
}
