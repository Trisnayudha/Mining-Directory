<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimCompany extends Model
{
    use HasFactory;

    protected $table = 'claim_company';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'full_name',
        'position_title',
        'email',
        'alternate_email',
        'code_phone',
        'phone',
        'company_name',
        'company_category',
        'classification_company',
        'project_type',
        'company_address',
        'city',
        'state',
        'country',
        'postal_code',
        'code_company_phone',
        'company_phone_number',
        'company_email',
        'company_website',
    ];
}
