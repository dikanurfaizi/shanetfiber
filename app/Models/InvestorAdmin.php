<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvestorAdmin extends Model
{
    use HasFactory;

    protected $table = 'investor_admins';

    protected $fillable = [
        'investor_id',
        'admin_id',
        'linked_at',
        'created_by',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
    ];

    /**
     * Relasi ke user yang bertindak sebagai investor.
     */
    public function investor()
    {
        return $this->belongsTo(User::class, 'investor_id');
    }

    /**
     * Relasi ke user yang bertindak sebagai admin.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    /**
     * Relasi ke user yang membuat tautan ini (opsional).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
