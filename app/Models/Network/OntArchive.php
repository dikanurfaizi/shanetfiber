<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OntArchive extends Model
{
    use HasFactory;

    protected $table = 'ont_archives';

    // Tidak pakai auto increment
    public $incrementing = false;
    protected $primaryKey = null;
    protected $keyType = 'string';

    protected $fillable = [
        'source_olt_id',
        'ont_index',
        'pon_group',
        'name',
        'tx_power',
        'rx_power',
        'status',
        'mac_address',
        'temperature',
        'archived_at',
    ];

    public $timestamps = false; // karena hanya pakai archived_at

    public function olt()
    {
        return $this->belongsTo(Olt::class, 'source_olt_id');
    }

    /**
     * Override save untuk memastikan insert selalu berhasil
     * karena tidak ada single PK.
     */
    public function save(array $options = [])
    {
        if (!$this->exists) {
            return $this->newQuery()->insert($this->attributesToArray());
        }

        return parent::save($options);
    }
}
