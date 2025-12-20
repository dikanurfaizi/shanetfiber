<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {

            /**
             * 1. Add source_type
             *    nullable dulu supaya tidak break existing data
             */
            $table->string('source_type', 50)
                ->nullable()
                ->after('item_type');

            /**
             * 2. Rename item_id → source_id
             */
            $table->renameColumn('item_id', 'source_id');
        });
    }

    public function down(): void
    {
        Schema::table('billing_items', function (Blueprint $table) {

            /**
             * Rollback rename
             */
            $table->renameColumn('source_id', 'item_id');

            /**
             * Drop source_type
             */
            $table->dropColumn('source_type');
        });
    }
};
