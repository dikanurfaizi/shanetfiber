<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->foreignId('paket_category_id')
                ->nullable()
                ->constrained('paket_categories')
                ->after('billing_cycle_id');
        });
    }

    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->dropConstrainedForeignId('package_category_id');
        });
    }
};
