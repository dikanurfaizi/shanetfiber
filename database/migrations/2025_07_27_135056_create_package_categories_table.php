<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paket_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');           // Example: "Home", "Business", etc.
            $table->string('slug')->unique(); // For URL or reference
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_categories');
    }
};
