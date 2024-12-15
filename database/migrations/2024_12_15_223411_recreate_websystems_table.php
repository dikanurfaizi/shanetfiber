<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RecreateWebsystemsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the table if it exists
        if (Schema::hasTable('websystems')) {
            Schema::dropIfExists('websystems');
        }

        // Create the table again
        Schema::create('websystems', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('version');
            $table->string('cpanel_url')->nullable();
            $table->timestamps();
            $table->enum('payment_gateway', ['enable', 'disable'])->default('disable');
            $table->boolean('enable_tax')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->string('email')->nullable();
            $table->string('url')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('country_code', 3)->nullable();
            $table->string('phone')->nullable();
            $table->string('customer_code')->nullable();
            $table->integer('long_customer_code')->nullable();
        });

        // Insert the default value
        DB::table('websystems')->insert([
            'id' => 1,
            'slug' => 'websystem',
            'title' => 'ShaNet Fiber Management',
            'version' => '1.0.9b3',
            'cpanel_url' => null,
            'created_at' => '2024-09-29 08:50:11',
            'updated_at' => '2024-11-15 04:08:11',
            'payment_gateway' => 'disable',
            'enable_tax' => false,
            'tax_rate' => 11.00,
            'email' => 'd_ardi@ymail.com',
            'url' => null,
            'address' => 'Jl. Sukamaju No. 49 RT 09/07 Kelurahan Ciseureuh',
            'city' => 'Kabupaten Purwakarta',
            'postal_code' => '41118',
            'country_code' => 'IDN',
            'phone' => '085795909904',
            'customer_code' => 'SNF',
            'long_customer_code' => 3,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the table if rolling back
        Schema::dropIfExists('websystems');
    }
}
