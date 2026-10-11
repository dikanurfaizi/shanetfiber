<?php

namespace App\Services\WhatsApp\Meta;

use App\Models\WhatsApp\WhatsAppConversation;
use Illuminate\Support\Facades\DB;

class AcquiredConversationLock
{
    public function handle(int $conversationId, int $adminId): bool
    {
        return DB::transaction(function () use ($conversationId, $adminId) {

            $conversation = WhatsAppConversation::lockForUpdate()->find($conversationId);

            if (!$conversation) {
                return false;
            }

            // Jika belum ada lock
            if (!$conversation->locked_by_admin_id) {

                $conversation->update([
                    'locked_by_admin_id' => $adminId,
                    'locked_at' => now(),
                ]);

                return true;
            }

            // Jika sudah lock oleh admin yang sama
            if ($conversation->locked_by_admin_id === $adminId) {
                return true;
            }

            // Sudah lock oleh admin lain
            return false;
        });
    }
}
