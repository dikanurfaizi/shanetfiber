<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiUrl extends Model
{
    use HasFactory;

    // Jika nama tabel berbeda dari konvensi Laravel, tentukan di sini
    protected $table = 'api_urls';

    protected $fillable = [
        'name',
        'url',
    ];
}
