<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat View radcheck (Otentikasi Username & Password PPP)
        DB::statement("
            CREATE OR REPLACE VIEW radcheck AS
            SELECT 
                c.id AS id,
                c.username AS username,
                'Cleartext-Password' AS attribute,
                ':=' AS op,
                c.password_ppp AS value
            FROM customers c
            INNER JOIN customer_subscriptions cs ON cs.customer_id = c.id
            WHERE c.disabled = 'false'
              AND c.deleted_at IS NULL
              AND cs.status = 'active'
              AND cs.deleted_at IS NULL
              AND c.username IS NOT NULL 
              AND c.username != ''
        ");

        // 2. Buat View radreply (Otorisasi PPP Profile & IP Statis)
        DB::statement("
            CREATE OR REPLACE VIEW radreply AS
            -- Mengirim Nama Profile MikroTik (diambil dari packages.profile)
            SELECT 
                c.id AS id,
                c.username AS username,
                'Mikrotik-Group' AS attribute,
                ':=' AS op,
                p.profile AS value
            FROM customers c
            INNER JOIN customer_subscriptions cs ON cs.customer_id = c.id
            INNER JOIN packages p ON cs.package_id = p.id
            WHERE c.disabled = 'false'
              AND c.deleted_at IS NULL
              AND cs.status = 'active'
              AND cs.deleted_at IS NULL
              AND c.username IS NOT NULL 
              AND c.username != ''

            UNION ALL

            -- Mengirim IP Statis (jika ip_static diisi)
            SELECT 
                c.id + 1000000 AS id,
                c.username AS username,
                'Framed-IP-Address' AS attribute,
                ':=' AS op,
                c.ip_static AS value
            FROM customers c
            INNER JOIN customer_subscriptions cs ON cs.customer_id = c.id
            WHERE c.disabled = 'false'
              AND c.deleted_at IS NULL
              AND cs.status = 'active'
              AND cs.deleted_at IS NULL
              AND c.ip_static IS NOT NULL 
              AND c.ip_static != ''
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus view jika migration di-rollback
        DB::statement("DROP VIEW IF EXISTS radreply");
        DB::statement("DROP VIEW IF EXISTS radcheck");
    }
};
