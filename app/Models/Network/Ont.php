<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ont extends Model
{
    use HasFactory;

    protected $table = 'onts';

    // Tidak ada kolom auto increment
    public $incrementing = false;

    // Tidak ada single primary key
    protected $primaryKey = null;

    // Kalau key bukan integer (karena ont_index string), disable type casting ke int
    protected $keyType = 'string';

    protected $fillable = [
        'olt_id',
        'ont_index',
        'pon_group',
        'name',
        'tx_power',
        'rx_power',
        'status',
        'last_updated',
        'mac_address',
        'temperature',
    ];

    public $timestamps = true;

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'olt_id');
    }

    /**
     * Override save() behaviour agar tidak error
     * karena Laravel butuh key untuk menentukan insert/update.
     * Kita pakai forceCreate untuk semua kasus.
     */
    public function save(array $options = [])
    {
        if (!$this->exists) {
            return $this->newQuery()->insert($this->attributesToArray());
        }
        return parent::save($options);
    }
}
