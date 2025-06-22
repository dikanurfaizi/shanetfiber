<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('telegram_messages')) {
            DB::table('telegram_messages')->delete(); // Menghapus semua data dalam tabel
        }

        if (!Schema::hasTable('telegram_messages')) {
            Schema::create('telegram_messages', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->text('incoming_message')->nullable(true);
                $table->text('reply_message');
                $table->string('description')->nullable(true);
                $table->boolean('default')->default(false);
                $table->boolean('disabled')->default(false);
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_messages');
    }
};
