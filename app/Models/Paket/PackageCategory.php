<?php

namespace App\Models\Paket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Paket;

class PackageCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'paket_categories';

    public function pakets()
    {
        return $this->hasMany(Paket::class);
    }
}
