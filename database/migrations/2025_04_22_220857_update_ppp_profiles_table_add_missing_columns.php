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
        Schema::table('ppp_profiles', function (Blueprint $table) {
            // Drop foreign key dan kolom mikrotik_id jika ada
            if (Schema::hasColumn('ppp_profiles', 'mikrotik_id')) {
                $table->dropForeign(['mikrotik_id']);
                $table->dropColumn('mikrotik_id');
            }

            if (!Schema::hasColumn('ppp_profiles', 'bridge_learning')) {
                $table->enum('bridge_learning', ['no', 'yes', 'required', 'default'])->nullable()->after('remote_address');
            }

            if (!Schema::hasColumn('ppp_profiles', 'change_tcp_mss')) {
                $table->enum('change_tcp_mss', ['no', 'yes', 'required', 'default'])->nullable()->after('only_one');
            }

            if (!Schema::hasColumn('ppp_profiles', 'use_upnp')) {
                $table->enum('use_upnp', ['no', 'yes', 'required', 'default'])->nullable()->after('change_tcp_mss');
            }

            if (!Schema::hasColumn('ppp_profiles', 'rate_limit')) {
                $table->string('rate_limit')->nullable()->after('use_upnp');
            }

            if (!Schema::hasColumn('ppp_profiles', 'address_list')) {
                $table->string('address_list')->nullable()->after('queue_type');
            }

            if (!Schema::hasColumn('ppp_profiles', 'default')) {
                $table->boolean('default')->default(false)->after('script_on_down');
            }

            if (!Schema::hasColumn('ppp_profiles', 'comment')) {
                $table->string('comment')->nullable()->after('default');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ppp_profiles', function (Blueprint $table) {
            $columns = [
                'bridge_learning',
                'change_tcp_mss',
                'use_upnp',
                'rate_limit',
                'address_list',
                'default',
                'comment',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('ppp_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (!Schema::hasColumn('ppp_profiles', 'mikrotik_id')) {
                $table->unsignedBigInteger('mikrotik_id')->nullable()->after('id');
                $table->foreign('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('cascade');
            }
        });
    }
};
