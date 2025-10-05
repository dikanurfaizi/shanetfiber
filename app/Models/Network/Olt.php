<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Helpers\OltHelper;

class Olt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'olts';

    protected $primaryKey = 'id';

    protected $casts = [
        'status' => 'integer',
    ];

    protected $fillable = [
        'name',
        'ip',
        'port',
        'community',
        'version',
        'oid_mapping_id',
        'status',
        'latitude',
        'longitude'
    ];

    public function onts()
    {
        return $this->hasMany(Ont::class, 'olt_id');
    }

    public function getTotalOntAttribute(): int
    {
        return $this->onts->count();
    }

    public function updateLogs()
    {
        return $this->hasMany(OltUpdateLog::class, 'olt_id');
    }

    public function latestUpdateLog()
    {
        return $this->hasOne(OltUpdateLog::class, 'olt_id')->latest('updated_at');
    }

    public function updateStatus(int $status): bool
    {
        $this->status = $status;
        return $this->save();
    }

    // 🔹 Relasi baru ke OidMapping
    public function mapping()
    {
        return $this->belongsTo(OidMapping::class, 'oid_mapping_id');
    }

    // 🔹 Helper untuk langsung ambil vendor
    public function getVendorAttribute(): ?string
    {
        return $this->mapping?->vendor;
    }

    public function getStatusTextAttribute(): string
    {
        return OltHelper::getDisplayStatus($this->status);
    }
}
