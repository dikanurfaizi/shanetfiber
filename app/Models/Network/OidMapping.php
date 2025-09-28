<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OidMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'oid_mappings';

    protected $fillable = [
        'vendor',
        'model',
        'description',
    ];

    public $timestamps = true;

    /**
     * Relasi ke OidMappingDetail (1:N)
     */
    public function details()
    {
        return $this->hasMany(OidMappingDetail::class, 'mapping_id');
    }

    /**
     * Helper untuk ambil OID berdasarkan key
     */
    public function getOid(string $key): ?string
    {
        $detail = $this->details->firstWhere('oid_key', $key);
        return $detail?->oid_value;
    }
}
