<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
