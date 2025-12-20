<?php

namespace App\Models\Customers;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeletedCustomer extends Model
{
    use HasFactory;
    public $guarded = [];
}
