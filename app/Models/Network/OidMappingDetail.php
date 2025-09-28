<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OidMappingDetail extends Model
{
    use HasFactory;

    protected $table = 'oid_mapping_details';

    protected $fillable = [
        'mapping_id',
        'oid_key',
        'oid_value',
        'description',
    ];

    public $timestamps = true;

    /**
     * Relasi balik ke OidMapping (N:1)
     */
    public function mapping()
    {
        return $this->belongsTo(OidMapping::class, 'mapping_id');
    }
}
