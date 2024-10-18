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
        Schema::create('ppp_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('cascade');
            $table->string('profile_id')->nullable();
            //general
            $table->string('profile_name')->nullable();
            $table->string('local_address')->nullable();
            $table->string('remote_address')->nullable();
            $table->string('dns_server')->nullable();

            //protocol
            $table->enum('use_ipv6', ['no', 'yes', 'required', 'default'])->default('yes');
            $table->enum('use_mpls', ['no', 'yes', 'required', 'default'])->default('default');
            $table->enum('use_encryption', ['no', 'yes', 'required', 'default'])->default('default');
            $table->enum('use_compression', ['no', 'yes', 'default'])->default('default');

            //limit
            $table->string('max_upload')->nullable();
            $table->string('max_download')->nullable();
            $table->string('min_upload')->nullable();
            $table->string('min_download')->nullable();
            $table->string('burst_upload')->nullable();
            $table->string('burst_download')->nullable();
            $table->string('burst_threshold_upload')->nullable();
            $table->string('burst_threshold_download')->nullable();
            $table->string('burst_time_upload')->nullable();
            $table->string('burst_time_download')->nullable();
            $table->enum('priority', ['1', '2', '3', '4', '5', '6', '7', '8'])->default('8');
            $table->string('rasio_cir')->nullable();
            $table->string('session_timeout')->nullable();
            $table->string('idle_timeout')->nullable();
            $table->enum('only_one', ['no', 'yes', 'default'])->default('default');

            //queue
            $table->string('insert_queue_before')->nullable();
            $table->string('parent_queue')->nullable();
            $table->string('queue_type')->nullable();

            //queue
            $table->string('script_on_up')->nullable();
            $table->string('script_on_down')->nullable();
            $table->enum('disabled', ['false', 'true'])->default('false');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ppp_profiles');
    }
};
