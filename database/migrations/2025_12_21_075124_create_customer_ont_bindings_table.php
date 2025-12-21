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
        Schema::create('customer_ont_bindings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();

            // Identitas ONT saat binding
            $table->string('ont_mac', 32);
            $table->string('ont_name')->nullable();
            $table->string('olt_name')->nullable();

            // Metadata binding
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('bound_at');
            $table->timestamp('unbound_at')->nullable();

            // Audit
            $table->foreignId('bound_by')->nullable()->constrained('users');
            $table->foreignId('unbound_by')->nullable()->constrained('users');
            $table->string('reason')->nullable();

            $table->timestamps();

            $table->unique(['customer_id', 'status'], 'uniq_active_binding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_ont_bindings');
    }
};
