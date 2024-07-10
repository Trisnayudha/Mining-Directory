<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqHome extends Model
{
    use  HasFactory;
    protected $table = 'faq_home_page';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'desciprtion',
        'category',
    ];
}
