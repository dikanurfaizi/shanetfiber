<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Models\Billings\BillingPayment;
use App\Models\Customers\Customer;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Receipt extends Model
{
    use SoftDeletes, HasFactory;
    protected $dates = ['deleted_at'];
    public $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function billingPayment()
    {
        return $this->belongsTo(BillingPayment::class);
    }

    public function items()
    {
        return $this->hasMany(ReceiptItem::class);
    }

    public function addons()
    {
        return $this->items
            ->where('type', 'addon')
            ->values();
    }

    public function packageItem()
    {
        return $this->items->where('type', 'package')->first();
    }
}
