<?php

namespace App\Models\Packages;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Packages\Package;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackageCategories extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }
}
