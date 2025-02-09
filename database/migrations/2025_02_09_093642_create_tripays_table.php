<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\TripaySeeder;
use App\Models\PaymentGateway;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('tripays')) {
            Schema::table('tripays', function (Blueprint $table) {
                if (!Schema::hasColumn('tripays', 'slug')) {
                    $table->string('slug')->unique();
                }
                if (!Schema::hasColumn('tripays', 'name')) {
                    $table->string('name');
                }
                if (!Schema::hasColumn('tripays', 'tripays_merchant_id')) {
                    $table->string('tripays_merchant_id')->nullable();
                }
                if (!Schema::hasColumn('tripays', 'tripays_api_key')) {
                    $table->string('tripays_api_key')->nullable();
                }
                if (!Schema::hasColumn('tripays', 'tripays_private_key')) {
                    $table->string('tripays_private_key')->nullable();
                }
                if (!Schema::hasColumn('tripays', 'tripays_sandbox_api_key')) {
                    $table->string('tripays_sandbox_api_key')->nullable();
                }
                if (!Schema::hasColumn('tripays', 'tripays_sandbox_private_key')) {
                    $table->string('tripays_sandbox_private_key')->nullable();
                }
                if (!Schema::hasColumn('tripays', 'tripays_is_production')) {
                    $table->enum('tripays_is_production', ['false', 'true'])->default('false');
                }
                if (!Schema::hasColumn('tripays', 'disabled')) {
                    $table->enum('disabled', ['false', 'true'])->default('true');
                }
            });
        } else {
            Schema::create('tripays', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->string('tripays_merchant_id')->nullable();
                $table->string('tripays_api_key')->nullable();
                $table->string('tripays_private_key')->nullable();
                $table->string('tripays_sandbox_api_key')->nullable();
                $table->string('tripays_sandbox_private_key')->nullable();
                $table->enum('tripays_is_production', ['false', 'true'])->default('false');
                $table->enum('disabled', ['false', 'true'])->default('true');
                $table->timestamps();
            });
        }

        (new TripaySeeder())->run();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tripays');
        PaymentGateway::where('slug', 'tripays')->delete();
    }
};
