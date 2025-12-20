<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {

            $table->unsignedBigInteger('package_id_before')
                ->nullable()
                ->after('billing_periode_id');

            $table->unsignedBigInteger('package_id_after')
                ->nullable()
                ->after('package_id_before');

            $table->date('package_changed_at')
                ->nullable()
                ->after('package_id_after');
        });
    }

    public function down(): void
    {
        Schema::table('billing_subscription_periodes', function (Blueprint $table) {

            $table->dropColumn([
                'package_id_before',
                'package_id_after',
                'package_changed_at',
            ]);
        });
    }
};
