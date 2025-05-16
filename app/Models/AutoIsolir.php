<?php

namespace App\Models;

use App\Models\Mikrotik;
use App\Models\Paket\PppProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AutoIsolir extends Model
{
    use HasFactory;
    public $guarded = [];


    public function mikrotik(): BelongsTo
    {
        return $this->belongsTo(Mikrotik::class);
    }

    public function profile()
    {
        return $this->belongsTo(PppProfile::class, 'profile_id');
    }
}
