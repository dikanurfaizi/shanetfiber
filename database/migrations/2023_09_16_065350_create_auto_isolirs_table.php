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
        if (!Schema::hasTable('auto_isolirs')) {
            Schema::create('auto_isolirs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('mikrotik_id')->references('id')->on('mikrotiks')->onDelete('cascade');
                $table->string('profile_id');
                $table->string('script_id');
                $table->string('schedule_id');
                $table->string('address_list_isolir')->nullable();
                $table->enum('activation_date', ['false', 'true'])->default('false');
                $table->string('due_date');
                $table->string('comment_unpayment');
                $table->string('comment_payment');
                $table->string('ros_version_id');
                $table->enum('disabled', ['false', 'true'])->default('true');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_isolirs');
    }
};
