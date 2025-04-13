<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToTables extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->index('disabled');
        });

        Schema::table('mikrotiks', function (Blueprint $table) {
            $table->index('disabled');
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->index('status');
        });

        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['disabled']);
        });

        Schema::table('mikrotiks', function (Blueprint $table) {
            $table->dropIndex(['disabled']);
        });

        Schema::table('billings', function (Blueprint $table) {
            $table->dropIndex(['status']);
        });

        Schema::table('billing_periodes', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
}
