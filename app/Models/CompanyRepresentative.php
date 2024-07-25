<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyRepresentative extends Model
{
    use  HasFactory;
    protected $table = 'company_representative';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'image',
        'email',
        'code_phone',
        'phone',
        'country',
        'job_title',
        'company_id',
    ];
}
