<?php

namespace App\Models\Telegram;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\User; // ✅ pastikan import model User

class TelegramChatId extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /**
     * Optional: Jika kamu ingin pakai slug sebagai key pada route binding
     * Hapus jika tidak pakai slug
     */
    public function getRouteKeyName(): string
    {
        return 'slug'; // Hanya jika kamu memang punya kolom `slug`
    }

    /**
     * Relasi ke user (many telegram_chat_ids belongs to one user)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
