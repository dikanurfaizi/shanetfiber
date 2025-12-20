<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {

    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Rename table: subscription_durations → package_durations
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasTable('subscription_durations') &&
            !Schema::hasTable('package_durations')
        ) {

            Schema::rename('subscription_durations', 'package_durations');
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Create duration_labels master table
        |--------------------------------------------------------------------------
        */
        if (!Schema::hasTable('duration_labels')) {
            Schema::create('duration_labels', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->unsignedTinyInteger('duration_months');
                $table->string('name_en');
                $table->string('name_id');
                $table->timestamps();
            });
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Add duration_id column
        |--------------------------------------------------------------------------
        */
        Schema::table('package_durations', function (Blueprint $table) {
            if (!Schema::hasColumn('package_durations', 'duration_id')) {
                $table->unsignedBigInteger('duration_id')
                    ->nullable()
                    ->after('package_id');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 4. Migrate old duration label data → duration_labels
        |---------------------------------------------------------------------------
        | FIX: Hindari duplicate insert jika migration dijalankan ulang.
        |--------------------------------------------------------------------------
        */
        $oldRows = DB::table('package_durations')->get();

        foreach ($oldRows as $row) {

            $slug = strtolower(str_replace(' ', '-', $row->name_en));

            // FIX: Hindari duplicate slug
            $existing = DB::table('duration_labels')->where('slug', $slug)->first();

            if ($existing) {
                $durationId = $existing->id;
            } else {
                $durationId = DB::table('duration_labels')->insertGetId([
                    'slug'            => $slug,
                    'duration_months' => $row->duration_months,
                    'name_en'         => $row->name_en,
                    'name_id'         => $row->name_id,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            // Update FK
            DB::table('package_durations')
                ->where('id', $row->id)
                ->update(['duration_id' => $durationId]);
        }

        /*
        |--------------------------------------------------------------------------
        | 5. Add duration_id foreign key
        |--------------------------------------------------------------------------
        */
        Schema::table('package_durations', function (Blueprint $table) {

            // FIX: Wrap FK creation in try/catch, because this can fail if FK already exists
            try {
                $table->foreign('duration_id')
                    ->references('id')
                    ->on('duration_labels')
                    ->cascadeOnDelete();
            } catch (\Throwable $e) {
                // ignore duplicate FK creation
            }
        });

        /*
        |--------------------------------------------------------------------------
        | 6. Drop old redundant columns
        |--------------------------------------------------------------------------
        */
        Schema::table('package_durations', function (Blueprint $table) {
            foreach (['name_en', 'name_id', 'duration_months'] as $col) {
                if (Schema::hasColumn('package_durations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Restore old columns
        |--------------------------------------------------------------------------
        */
        Schema::table('package_durations', function (Blueprint $table) {
            if (!Schema::hasColumn('package_durations', 'name_en')) {
                $table->string('name_en')->nullable();
            }
            if (!Schema::hasColumn('package_durations', 'name_id')) {
                $table->string('name_id')->nullable();
            }
            if (!Schema::hasColumn('package_durations', 'duration_months')) {
                $table->unsignedTinyInteger('duration_months')->nullable();
            }

            if (Schema::hasColumn('package_durations', 'duration_id')) {
                try {
                    $table->dropForeign(['duration_id']);
                } catch (\Throwable $e) {
                }
                $table->dropColumn('duration_id');
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Restore data from duration_labels
        |--------------------------------------------------------------------------
        */
        if (Schema::hasTable('duration_labels')) {

            $rows = DB::table('duration_labels')->get();

            foreach ($rows as $label) {
                DB::table('package_durations')
                    ->where('duration_id', $label->id)
                    ->update([
                        'name_en'         => $label->name_en,
                        'name_id'         => $label->name_id,
                        'duration_months' => $label->duration_months,
                    ]);
            }
        }

        Schema::dropIfExists('duration_labels');

        /*
        |--------------------------------------------------------------------------
        | Rename table back
        |--------------------------------------------------------------------------
        */
        if (
            Schema::hasTable('package_durations') &&
            !Schema::hasTable('subscription_durations')
        ) {

            Schema::rename('package_durations', 'subscription_durations');
        }
    }
};
