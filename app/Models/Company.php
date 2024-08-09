<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Company extends Model implements AuthenticatableContract, JWTSubject
{
    use Authenticatable;
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
        'email',
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
        'name_representative',
        'job_title_representative',
        'prefix_phone_representative',
        'phone_representative',
        'verify_email',
        'password'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function companyCategories()
    {
        return $this->hasMany(CompanyCategory::class, 'company_id', 'id');
    }
}
