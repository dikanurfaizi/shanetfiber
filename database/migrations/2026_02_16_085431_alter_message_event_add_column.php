<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_events', function (Blueprint $table) {
            $table->json('request_payload')->nullable()->after('message_id');
            $table->json('response_payload')->nullable()->after('request_payload');
            $table->unsignedInteger('retry_count')->default(0)->after('response_payload');
            $table->timestamp('last_error_at')->nullable()->after('retry_count');

            // Optional
            $table->string('stage')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('message_events', function (Blueprint $table) {
            $table->dropColumn([
                'request_payload',
                'response_payload',
                'retry_count',
                'last_error_at',
                'stage',
            ]);
        });
    }
};
