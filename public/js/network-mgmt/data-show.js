// Simple JavaScript for interactivity
document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    tooltipTriggerList.map((el) => new bootstrap.Tooltip(el));

    const refreshBtn = document.getElementById("refresh-data-btn");

    if (refreshBtn) {
        refreshBtn.addEventListener("click", function () {
            const oltId = this.dataset.oltId;
            this.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Refreshing...';
            this.disabled = true;

            fetch(`/cpanel/network/oltmanagements/${oltId}/refresh-data`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Ganti konten container dengan partial yang baru
                        document.getElementById("olt-details-container").innerHTML = data.html;

                        // ✅ Toast sukses
                        new Notify({
                            status: 'success',
                            title: 'Updated Data',
                            text: 'OLT data has been successfully updated.',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 3000,
                            autoclose: true
                        });
                    } else {
                        // ❌ Toast gagal dari server response
                        new Notify({
                            status: 'error',
                            title: 'Failed to Load Data',
                            text: data.error || 'An error occurred on the server.',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    }
                })
                .catch((error) => {
                    // ❌ Toast gagal koneksi
                    new Notify({
                        status: 'error',
                        title: 'Failed to Connect!',
                        text: error.message || 'A network error occurred.',
                        effect: 'slide',
                        speed: 500,
                        showCloseButton: true,
                        autotimeout: 5000,
                        autoclose: true
                    });
                })
                .finally(() => {
                    // Selalu restore tombol
                    refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                    refreshBtn.disabled = false;
                });
        });
    }

    // Taruh sekali saja (mis. di data-show.js), BUKAN di partial
    document.addEventListener("click", (e) => {
        const card = e.target.closest("#olt-details-container .summary-card");
        if (!card) return;

        const status = card.dataset.status;

        // Mulai dari URL saat ini agar semua query yang ada tetap terbawa (pon_group, perPage, search, dst.)
        const url = new URL(window.location.href);
        url.searchParams.set("status", status);
        url.searchParams.set("page", 1);

        // (opsional) fallback kalau pon_group/perPage tidak ada di URL, ambil dari data-* di container
        const container = document.getElementById("olt-details-container");
        if (container) {
            if (!url.searchParams.has("pon_group") && container.dataset.ponGroup) {
                url.searchParams.set("pon_group", container.dataset.ponGroup);
            }
            if (!url.searchParams.has("perPage") && container.dataset.perPage) {
                url.searchParams.set("perPage", container.dataset.perPage);
            }
        }

        window.location.href = url.toString();
    });

    document.addEventListener("click", async (e) => {
        const btn = e.target.closest(".scan-button");
        if (!btn) return;

        e.preventDefault();

        const oltId = btn.dataset.oltId;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> Scanning...';
        btn.disabled = true;

        try {
            // 1️⃣ Jalankan proses scan
            const scanRes = await fetch(`/cpanel/network/oltmanagements/scan/olt/${oltId}`);
            const scanJson = await scanRes.json();

            // normalisasi struktur data
            const scanData = scanJson.result || scanJson;
            const isScanSuccess = scanData.error_code === "200";

            if (!isScanSuccess) {
                new Notify({
                    status: 'error',
                    title: 'Scan OLT Gagal',
                    text: scanData.message || 'Terjadi kesalahan saat scanning OLT.',
                    effect: 'slide',
                    speed: 500,
                    showCloseButton: true,
                    autotimeout: 5000,
                    autoclose: true
                });
                return;
            }

            // 2️⃣ Kalau berhasil, refresh data OLT
            const refreshRes = await fetch(`/cpanel/network/oltmanagements/refresh/data/${oltId}`);
            const refreshData = await refreshRes.json();

            if (!refreshData.success) {
                new Notify({
                    status: 'error',
                    title: 'Failed to Load OLT Data',
                    text: refreshData.error || 'Failed to update OLT data from the server.',
                    effect: 'slide',
                    speed: 500,
                    showCloseButton: true,
                    autotimeout: 5000,
                    autoclose: true
                });
                return;
            }

            // 3️⃣ Jika berhasil semua, replace tampilan & tampilkan toast sukses
            document.getElementById("olt-details-container").innerHTML = refreshData.html;

            // 🕒 Update label "Last scan"
            if (refreshData.last_scan) {
                const lastScanEl = document.querySelector(".status-indicator");
                if (lastScanEl) {
                    lastScanEl.innerHTML = `<i class="fas fa-history"></i> Last scan: ${refreshData.last_scan}`;
                }
            }

            new Notify({
                status: 'success',
                title: 'Successfully Scanned and Updated',
                text: 'OLT data has been updated successfully.',
                effect: 'slide',
                speed: 500,
                showCloseButton: true,
                autotimeout: 3000,
                autoclose: true
            });

        } catch (err) {
            // 4️⃣ Tangani error jaringan / fetch
            new Notify({
                status: 'error',
                title: 'Connection Failed',
                text: err.message || 'A network error has occurred.',
                effect: 'slide',
                speed: 500,
                showCloseButton: true,
                autotimeout: 5000,
                autoclose: true
            });
        } finally {
            // 5️⃣ Pulihkan tombol apapun hasilnya
            btn.innerHTML = '<i class="fas fa-sync-alt"></i> Scan OLT';
            btn.disabled = false;
        }
    });
});
