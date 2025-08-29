<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'customer_id',
        'activity_type',
        'field_name',
        'old_value',
        'new_value',
        'ip_address',
        'user_agent'
    ];

    // Helper method untuk mencatat perubahan
    public static function logChange(
        $customerId,
        $activityType,
        $fieldName,
        $oldValue,
        $newValue,
        $ip = null,
        $userAgent = null
    ) {
        // Jangan log jika nilai tidak berubah
        if ($oldValue === $newValue) {
            return;
        }

        return self::create([
            'customer_id' => $customerId,
            'activity_type' => $activityType,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'ip_address' => $ip,
            'user_agent' => $userAgent
        ]);
    }

    // Relasi ke customer
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
