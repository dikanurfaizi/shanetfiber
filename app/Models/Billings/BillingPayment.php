<?php

namespace App\Models\Billings;

use App\Models\Receipt;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class BillingPayment extends Model
{
    protected $fillable = [
        'billing_id',
        'amount_paid',
        'payment_method',
        'reference_id',
        'paid_by_user_id',
        'payment_time',
    ];

    public function billing()
    {
        return $this->belongsTo(Billing::class);
    }

    public function paidBy()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }

    public function receipt()
    {
        return $this->hasOne(Receipt::class);
    }

    public function paidByUser()
    {
        return $this->belongsTo(User::class, 'paid_by_user_id');
    }
}
