<?php

namespace App\Models\Network;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Olt extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'olts';

    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE   = 1;
    const STATUS_SCANNING = 2;

    const STRING_STATUS_INACTIVE = 'INACTIVE';
    const STRING_STATUS_ACTIVE   = 'ACTIVE';
    const STRING_STATUS_SCANNING = 'SCANNING';

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
        'vendor',
        'status',
        'latitude',
        'longitude'
    ];

    public function onus()
    {
        return $this->hasMany(Onu::class, 'olt_id');
    }

    public function getTotalOnuAttribute(): int
    {
        return $this->onus->count();
    }

    public function updateLogs()
    {
        return $this->hasMany(OltUpdateLog::class, 'olt_id');
    }

    public function latestUpdateLog()
    {
        return $this->hasOne(OltUpdateLog::class, 'olt_id')->latest('updated_at');
    }

    public function getDisplayStatusAttribute(): string
    {
        $status = optional($this->latestUpdateLog)->status;

        return match ($status) {
            'Up'   => self::STRING_STATUS_ACTIVE,
            'Down' => self::STRING_STATUS_INACTIVE,
            default => self::STRING_STATUS_SCANNING,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_ACTIVE   => 'Active',
            self::STATUS_SCANNING => 'Scanning',
            default               => 'Inactive',
        };
    }

    public function updateStatus(int $status): bool
    {
        $this->status = $status;
        return $this->save();
    }
}
