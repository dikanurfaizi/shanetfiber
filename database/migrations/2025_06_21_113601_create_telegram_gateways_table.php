<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('telegram_gateways')) {
            DB::table('telegram_gateways')->delete(); // Menghapus data dengan mematuhi foreign key
            Schema::dropIfExists('telegram_gateways');
        }

        if (!Schema::hasTable('telegram_gateways')) {
            Schema::create('telegram_gateways', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('description')->nullable();
                $table->string('url_callback')->nullable(true);
                $table->string('token')->nullable(true);
                $table->string('cert_file')->nullable(true);
                $table->boolean('disabled')->default(true);
                $table->string('basic_auth_username')->nullable();
                $table->string('basic_auth_password')->nullable();
                $table->enum('auth_disabled', ['true', 'false'])->default('false');
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
        Schema::dropIfExists('telegram_gateways');
    }
};
