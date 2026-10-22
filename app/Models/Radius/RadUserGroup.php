<?php

namespace App\Models\Radius;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RadUserGroup extends Model
{
    use HasFactory;

    /**
     * Nama koneksi database FreeRADIUS.
     */
    protected $connection = 'mysql_radius';

    /**
     * Nama tabel.
     */
    protected $table = 'radusergroup';

    /**
     * Tabel ini tidak memiliki primary key auto-incrementing id bawaan Laravel.
     */
    protected $primaryKey = 'username';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Menandakan jika tabel tidak memiliki kolom created_at dan updated_at.
     */
    public $timestamps = false;

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'username',
        'groupname',
        'priority',
    ];
}
