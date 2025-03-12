<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable = ['type', 'discount', 'slug', 'status'];

    public function getStatusLabel()
    {
        return $this->status === 1 ? 'Active' : 'Inactive';
    }

    public function getStatusClass()
    {
        return $this->status === 1 ? 'bg-success' : 'bg-danger';
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
