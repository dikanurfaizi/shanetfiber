<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('channel')->nullable()->after('email'); // email / whatsapp
            $table->string('otp', 6)->nullable()->after('token');
            $table->unsignedTinyInteger('attempt_count')->default(0)->after('otp'); // gagal input OTP
            $table->unsignedTinyInteger('resend_count')->default(0)->after('attempt_count'); // hitung resend
            $table->timestamp('cooldown_until')->nullable()->after('resend_count'); // kapan bisa coba lagi
        });
    }

    public function down(): void
    {
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->dropColumn([
                'channel',
                'otp',
                'attempt_count',
                'resend_count',
                'cooldown_until',
            ]);
        });
    }
};
