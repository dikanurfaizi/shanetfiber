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
        Schema::table('mikrotiks', function (Blueprint $table) {
            $table->string('radius_secret')->nullable()->after('password')
                ->comment('RADIUS Shared Secret untuk komunikasi FreeRADIUS & CoA');
            $table->integer('coa_port')->default(3799)->after('port')
                ->comment('Port CoA/PoD pada Router MikroTik (Default MikroTik: 3799)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotiks', function (Blueprint $table) {
            $table->dropColumn(['radius_secret', 'coa_port']);
        });
    }
};
