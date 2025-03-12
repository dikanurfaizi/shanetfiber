<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Tambahkan kolom baru
            $table->tinyInteger('billing_option')->default(0)->after('member_id');
            $table->string('billing_period')->nullable()->after('billing_option');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'billing_option')) {
                $table->dropColumn('billing_option');
            }
            if (Schema::hasColumn('customers', 'billing_period')) {
                $table->dropColumn('billing_period');
            }
        });
    }
};
