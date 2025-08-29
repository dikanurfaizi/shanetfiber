<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    use HasFactory;

    protected $table = 'password_history';
    protected $fillable = ['customer_id', 'password'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
