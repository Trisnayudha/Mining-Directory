<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyLog extends Model
{
    use HasFactory;

    protected $table = 'company_log';

    protected $fillable = [
        'company_id',
        'users_id'
    ];
}
