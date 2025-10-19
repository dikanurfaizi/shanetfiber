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
        Schema::create('investor_admins', function (Blueprint $table) {
            $table->id();

            // Foreign keys to users table
            $table->unsignedBigInteger('investor_id');
            $table->unsignedBigInteger('admin_id');

            // Optional metadata
            $table->timestamp('linked_at')->useCurrent();
            $table->unsignedBigInteger('created_by')->nullable();

            // Constraint & indexing
            $table->foreign('investor_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();

            $table->unique(['investor_id', 'admin_id'], 'unique_investor_admin');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investor_admins');
    }
};
