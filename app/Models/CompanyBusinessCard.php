<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyBusinessCard extends Model
{
    use  HasFactory;
    protected $table = 'company_users_buisnesscard';
    protected $fillable = [
        'users_id',
        'company_id',
        'status'
    ];
}
