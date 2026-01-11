# WhatsApp Template Module

Folder ini mengelola seluruh lifecycle WhatsApp template dalam 3 konteks terpisah:

## Integration

Berisi service untuk komunikasi langsung ke Meta WhatsApp API.
Contoh:

-   MetaTemplateFetcherService

Tujuan:

-   Fetch template dari Meta API
-   Tidak boleh mengakses database runtime payload
-   Tidak boleh digunakan oleh MessageDispatcher

---

## Sync

Berisi service untuk menyimpan hasil template Meta API ke database lokal.

Contoh:

-   TemplateSyncService

Tujuan:

-   Menjadi jembatan antara Integration dan Database
-   Database adalah source of truth untuk runtime rendering

---

## Runtime

Berisi logic untuk membangun pesan WhatsApp dari database template.

Contoh:

-   Commands
-   Handlers
-   TemplateContext
-   TemplateHandlerRegistry

Tujuan:

-   Mengubah domain command menjadi runtime context
-   Tidak boleh melakukan HTTP ke Meta API
-   Tidak boleh melakukan sinkronisasi template

---

## Prinsip Desain

1. Template database adalah source of truth.
2. Runtime tidak boleh bergantung ke Meta API.
3. Handler hanya mapping domain → runtime values.
4. PayloadBuilder yang membangun struktur Meta JSON.

Jika ingin menambah template baru:

1. Tambah Command
2. Tambah Handler
3. Register di TemplateHandlerRegistry

Jangan langsung memanggil PayloadBuilder dari luar Dispatcher.
