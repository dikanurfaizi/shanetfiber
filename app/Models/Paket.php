<?php

namespace App\Models;


use App\Models\Mikrotik;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Paket extends Model
{
    use HasFactory;
    public $guarded = [];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function mikrotik(): BelongsTo
    {
        return $this->belongsTo(Mikrotik::class);
    }

    //  public function getNamePriceAttribute()
    //{
    //     return $this->name . ' ' . $this->price;
    //  }
}
