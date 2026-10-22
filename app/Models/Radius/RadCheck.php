<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadCheck extends Model
{
    use HasFactory;

    /**
     * Nama koneksi database FreeRADIUS.
     */
    protected $connection = 'mysql_radius';

    /**
     * Nama tabel.
     */
    protected $table = 'radcheck';

    /**
     * Menandakan jika tabel tidak memiliki kolom created_at dan updated_at.
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'username',
        'attribute',
        'op',
        'value',
    ];
}
