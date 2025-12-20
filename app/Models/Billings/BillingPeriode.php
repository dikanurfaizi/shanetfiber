<?php

namespace App\Models\Billings;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingPeriode extends Model
{
    use HasFactory;
    public $guarded = [];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date'   => 'datetime',
    ];

    public function billingSubscriptionPeriodes()
    {
        return $this->hasMany(BillingSubscriptionPeriode::class, 'billing_periode_id');
    }

    public function startDate(): Carbon
    {
        return Carbon::createFromDate(
            $this->year,
            Carbon::parse($this->month)->month,
            1
        )->startOfDay();
    }

    public function endDate(): Carbon
    {
        return $this->startDate()->copy()->endOfMonth();
    }

    public function next(): BillingPeriode
    {
        return self::firstOrCreate(
            ['start_date' => $this->start_date->copy()->addMonth()->startOfMonth()],
            [
                'end_date' => $this->start_date->copy()->addMonth()->endOfMonth(),
                'status'   => 'draft',
            ]
        );
    }
}
