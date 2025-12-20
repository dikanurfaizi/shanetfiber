<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Drop foreign keys referencing members, if any still exist
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'member_id')) {
                try {
                    $table->dropForeign(['member_id']);
                } catch (\Throwable $e) {
                    // FK already removed or doesn't exist, ignore
                }
            }
        });

        // Finally drop the table
        Schema::dropIfExists('members');
    }

    public function down(): void
    {
        // Recreate the old members table structure (best effort)
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('type');
            $table->decimal('discount', 10, 2)->default(0);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Restore FK only if member_id exists
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'member_id')) {
                $table->foreign('member_id')
                    ->references('id')
                    ->on('members')
                    ->nullOnDelete();
            }
        });
    }
};
