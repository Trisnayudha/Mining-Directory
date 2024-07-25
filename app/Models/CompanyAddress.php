<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAddress extends Model
{
    use  HasFactory;
    protected $table = 'company_address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'town', 'country', 'company_id', 'phone', 'address'
    ];
}
