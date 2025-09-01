<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Websystem extends Model
{
    use HasFactory;
    public $guarded = [];

    protected $casts = [
        'enable_tax' => 'boolean',
        'tax_rate'   => 'float',
    ];
}
