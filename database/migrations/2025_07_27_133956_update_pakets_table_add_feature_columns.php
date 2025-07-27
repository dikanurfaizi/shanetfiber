<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            $table->foreignId('billing_cycle_id')
                ->nullable()
                ->constrained('billing_cycles')
                ->after('price');

            $table->integer('speed_up_to')->nullable()->after('billing_cycle_id');
            $table->string('speed_unit')->default('Mbps')->after('speed_up_to');
            $table->unsignedTinyInteger('recommended_device_min')->nullable()->after('speed_unit');
            $table->unsignedTinyInteger('recommended_device_max')->nullable()->after('recommended_device_min');
            $table->string('quota')->nullable()->after('recommended_device_max');
            $table->boolean('include_modem')->default(false)->after('quota');
            $table->integer('installation_fee')->default(0)->after('include_modem');
            $table->boolean('is_installation_free')->default(false)->after('installation_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pakets', function (Blueprint $table) {
            // drop foreign key dulu baru drop column
            $table->dropConstrainedForeignId('billing_cycle_id');

            $table->dropColumn([
                'speed_up_to',
                'speed_unit',
                'recommended_device_min',
                'recommended_device_max',
                'quota',
                'include_modem',
                'installation_fee',
                'is_installation_free'
            ]);
        });
    }
};
