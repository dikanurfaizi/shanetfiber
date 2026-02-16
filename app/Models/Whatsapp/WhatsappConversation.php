<?php

namespace App\Models\Whatsapp;

use App\Models\Customers\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WhatsappConversation extends Model
{
    protected $fillable = [
        'channel',
        'customer_number',
        'locked_by_admin_id',
        'locked_at',
        'has_admin_reply',
        'has_unread',
        'status',
        'last_message_at',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
        'locked_at' => 'datetime',
        'last_message_at' => 'datetime',
        'has_admin_reply' => 'boolean',
        'has_unread' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappWebhookMessage::class, 'conversation_id');
    }

    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by_admin_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isLocked(): bool
    {
        return $this->locked_by_admin_id !== null;
    }

    public function lock(int $adminId): void
    {
        $this->update([
            'locked_by_admin_id' => $adminId,
            'locked_at' => now(),
        ]);
    }

    public function unlock(): void
    {
        $this->update([
            'locked_by_admin_id' => null,
            'locked_at' => null,
        ]);
    }

    public function markAdminReplied(): void
    {
        $this->update([
            'has_admin_reply' => true,
            'has_unread' => false,
        ]);
    }

    public function markIncoming(): void
    {
        $this->update([
            'has_unread' => true,
            'has_admin_reply' => false,
        ]);
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'contact', 'customer_number');
    }

    // app/Models/WhatsappConversation.php
    public function getCustomerNameAttribute()
    {
        // Cek customer exact
        if ($this->customer) {
            return $this->customer->name;
        }

        // Fallback prefix 08 -> 62
        $normalized = $this->customer_number;
        if (Str::startsWith($normalized, '08')) {
            $normalized = '62' . substr($normalized, 1);
        } elseif (Str::startsWith($normalized, '62')) {
            $normalized = '0' . substr($normalized, 2);
        }

        $customer = Customer::where('contact', $normalized)->first();
        return $customer?->name ?? $this->customer_number;
    }
}
