<?php

namespace App\Models\Paket;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Paket\Paket;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaketCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $fillable = ['name', 'slug', 'description', 'is_active'];
    protected $table = 'paket_categories';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function pakets()
    {
        return $this->hasMany(Paket::class, 'paket_category_id');
    }
}
