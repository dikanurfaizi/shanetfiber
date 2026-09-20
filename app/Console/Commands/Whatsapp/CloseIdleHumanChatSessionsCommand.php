<?php

namespace App\Console\Commands\Whatsapp;

use Illuminate\Console\Command;
use App\Models\Whatsapp\WhatsappChatSession;
use App\Jobs\Whatsapp\CloseIdleHumanChatSessionsJob; // 👈 Import Job-nya
use App\Helpers\LogHelper;
use App\Helpers\ExecutionTimer;
use Carbon\Carbon;
use Illuminate\Support\Str;

class CloseIdleHumanChatSessionsCommand extends Command
{
    protected $signature = 'whatsapp:close-idle-sessions';
    protected $description = 'Mengecek sesi human chat WhatsApp yang idle lalu melemparnya ke Queue Job';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $traceId = (string) Str::uuid();
        $timer   = new ExecutionTimer();
        $timeoutMinutes = 15;
        $threshold = Carbon::now()->subMinutes($timeoutMinutes);

        // Ambil HANYA kolom 'id' saja agar payload data yang dilempar ke Queue sangat ringan
        $idleSessionIds = WhatsappChatSession::where('chat_mode', 'human')
            ->where('last_human_activity_at', '<=', $threshold)
            ->pluck('id')
            ->toArray();

        if (empty($idleSessionIds)) {
            LogHelper::logInfo(null, [
                'total_sessions_found' => count($idleSessionIds),
                'session_ids'          => $idleSessionIds
            ], 200, '[Command] No idle sessions found. No job dispatched.', $traceId, $timer->lap());
            return Command::SUCCESS;
        }

        CloseIdleHumanChatSessionsJob::dispatch($idleSessionIds, $traceId);

        LogHelper::logInfo(null, [
            'total_sessions_found' => count($idleSessionIds),
            'session_ids'          => $idleSessionIds
        ], 200, '[Command] Idle sessions found. Dispatched to CloseIdleHumanChatSessionsJob.', $traceId, $timer->lap());

        return Command::SUCCESS;
    }
}
