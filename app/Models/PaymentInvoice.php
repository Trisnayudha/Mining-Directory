<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'user_id',
        'company_id',
        'type',
        'pricing_period',
        'status',
        'merchant_name',
        'merchant_profile_picture_url',
        'amount',
        'payer_email',
        'description',
        'expiry_date',
        'invoice_url',
        'paid_amount',
        'bank_code',
        'paid_at',
        'fees_paid_amount',
    ];
}
