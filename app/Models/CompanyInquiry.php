<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyInquiry extends Model
{
    use  HasFactory;
    protected $table = 'company_inquiry';
    protected $fillable = [
        'name',
        'email',
        'type',
        'date',
        'message',
        'users_id',
        'company_id',
        'status'
    ];
}
