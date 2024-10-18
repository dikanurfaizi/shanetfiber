<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;
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
