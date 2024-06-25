<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use  HasFactory;
    protected $table = 'company';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'package',
        'company_name',
        'description',
        'location',
        'video',
        'image',
        'banner_image',
        'category_company',
        'slug',
        'email_company',
        'phone_company',
        'website',
        'facebook',
        'instagram',
        'linkedin',
        'value_1',
        'value_2',
        'value_3',
        'verify_company',
    ];
}
