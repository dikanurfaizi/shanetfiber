<?php

namespace App\Models;

use App\Models\MerkRouter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TypeRouter extends Model
{
    use HasFactory;
    public $guarded = [];

    public function merk_router(): BelongsTo
    {
        return $this->belongsTo(MerkRouter::class);
    }
}
