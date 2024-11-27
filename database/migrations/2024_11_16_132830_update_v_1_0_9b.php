<?php

use App\Models\AutoIsolir;
use App\Models\Customer;
use App\Models\Mikrotik;
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
        // Pastikan hanya perubahan yang benar-benar diperlukan diterapkan
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'internet_service')) {
                $table->string('internet_service')->nullable()->after('mikrotik_id');
            }
        });

        // Update nilai default untuk internet_service
        $customers = Customer::whereNull('internet_service')->get();
        foreach ($customers as $customer) {
            $customer->update(['internet_service' => 'ppp']);
        }

        Schema::table('pakets', function (Blueprint $table) {
            if (!Schema::hasColumn('pakets', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_isolirs', 'address_list_non_isolir')) {
                $table->string('address_list_non_isolir')->nullable();
            }
        });

        Schema::table('mikrotiks', function (Blueprint $table) {
            if (!Schema::hasColumn('mikrotiks', 'ros_version_id')) {
                $table->string('ros_version_id')->nullable();
            }
        });

        // Memindahkan data dari tabel auto_isolirs ke mikrotiks
        $mikrotiks = Mikrotik::all();
        foreach ($mikrotiks as $mikrotik) {
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotik->id)->first();
            if ($autoIsolir) {
                $mikrotik->update(['ros_version_id' => $autoIsolir->ros_version_id]);
            }
        }

        // Hapus kolom ros_version_id dari tabel auto_isolirs
        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (Schema::hasColumn('auto_isolirs', 'ros_version_id')) {
                $table->dropColumn('ros_version_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mikrotiks', function (Blueprint $table) {
            if (Schema::hasColumn('mikrotiks', 'ros_version_id')) {
                $table->dropColumn('ros_version_id');
            }
        });

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (!Schema::hasColumn('auto_isolirs', 'ros_version_id')) {
                $table->string('ros_version_id')->nullable();
            }
        });

        $mikrotiks = Mikrotik::all();
        foreach ($mikrotiks as $mikrotik) {
            $autoIsolir = AutoIsolir::where('mikrotik_id', $mikrotik->id)->first();
            if ($autoIsolir) {
                $autoIsolir->update(['ros_version_id' => $mikrotik->ros_version_id]);
            }
        }

        Schema::table('auto_isolirs', function (Blueprint $table) {
            if (Schema::hasColumn('auto_isolirs', 'address_list_non_isolir')) {
                $table->dropColumn('address_list_non_isolir');
            }
        });

        Schema::table('pakets', function (Blueprint $table) {
            if (Schema::hasColumn('pakets', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });

        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'internet_service')) {
                $table->dropColumn('internet_service');
            }
        });
    }
};
