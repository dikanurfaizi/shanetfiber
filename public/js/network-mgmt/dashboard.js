document.addEventListener("DOMContentLoaded", () => {
    const filterForm = document.getElementById("filter-form");

    document.querySelectorAll(".olt-status-wrapper").forEach((el) => {
        renderOltStatus(el, el.dataset.status);
    });

    filterForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const formData = new FormData(filterForm);

        try {
            const res = await fetch(filterForm.action, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
                body: formData,
            });

            const data = await res.json();

            // Update table berdasarkan device
            const isDesktop = window.innerWidth >= 768;
            if (isDesktop) {
                document.getElementById("olt-table-body").innerHTML =
                    data.table;
            } else {
                document.getElementById("olt-card-body").innerHTML = data.table;
            }

            // Update summary
            document.getElementById("olts-summary-wrapper").innerHTML =
                data.summary;

            // Re-init Bootstrap 5 dropdowns & tooltips setelah content diganti
            const dropdownElList = [].slice.call(
                document.querySelectorAll(
                    "#olt-table-body .dropdown-toggle, #olt-card-body .dropdown-toggle"
                )
            );
            dropdownElList.forEach((el) => new bootstrap.Dropdown(el));

            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll(
                    "#olt-table-body [data-bs-toggle='tooltip'], #olt-card-body [data-bs-toggle='tooltip']"
                )
            );
            tooltipTriggerList.forEach((el) => new bootstrap.Tooltip(el));
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "❌ Fetch Error",
                text: err.message || "An error occurred while sending data",
            });
        }
    });

    function renderOltStatus(wrapper, status) {
        if (!wrapper) return;

        const s = (status || "").toLowerCase();

        const indicator = wrapper.querySelector(".olt-status-indicator");
        const icon = indicator?.querySelector("i");
        const badge = wrapper.querySelector(".status-badge");

        if (!indicator || !icon || !badge) {
            console.warn("Invalid OLT status DOM structure", wrapper);
            return;
        }

        // reset
        indicator.className = "olt-status-indicator";
        badge.className = "badge status-badge";
        icon.className = "fas";

        if (!s) {
            icon.classList.add("fa-question-circle", "text-muted");
            badge.textContent = "UNKNOWN";
            return;
        }

        indicator.classList.add(s);
        badge.classList.add(s);
        badge.textContent = s.toUpperCase();

        const iconMap = {
            active: ["fa-check-circle", "text-success"],
            inactive: ["fa-times-circle", "text-danger"],
            scanning: ["fa-sync-alt", "fa-spin", "text-warning"],
        };

        const iconCfg = iconMap[s] ?? ["fa-question-circle", "text-muted"];
        icon.classList.add(...iconCfg);
    }

    function updateStatusClasses(element, status) {
        if (!element) return;
        const s = (status || "").toLowerCase();
        element.classList.remove("active", "inactive", "scanning");
        if (s) element.classList.add(s);
    }

    function handleOltAction(btn, updateTotalOnu = false) {
        btn.addEventListener("click", async (e) => {
            e.preventDefault();
            const url = btn.href;
            const originalHTML = btn.innerHTML;

            btn.innerHTML =
                '<i class="fas fa-spinner fa-spin"></i> Scanning...';
            btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method: "GET",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        Accept: "application/json",
                    },
                });

                let data = {};
                try {
                    data = await res.json();
                } catch (jsonErr) {
                    Swal.fire({
                        icon: "error",
                        title: "❌ JSON Parse Error",
                        text:
                            jsonErr.message ||
                            "An error occurred while reading the JSON response.",
                    });
                    return;
                }

                const status = data.body?.status || "Unknown";
                const oltId = data.body?.olt_id;
                const lastTime = data.body?.last_scan_time || "-";
                const lastDate = data.body?.last_scan_date || "-";

                const iconHtml =
                    {
                        Active: '<i class="fas fa-check-circle text-success"></i>',
                        Inactive:
                            '<i class="fas fa-times-circle text-danger"></i>',
                        Scanning:
                            '<i class="fas fa-sync-alt text-warning"></i>',
                    }[status] ||
                    '<i class="fas fa-question-circle text-muted"></i>';

                // Desktop
                const desktopRow = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .olt-status-indicator`
                );
                const desktopBadge = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .status-badge`
                );

                if (desktopRow) {
                    desktopRow.innerHTML = iconHtml;
                    updateStatusClasses(desktopRow, status);
                }
                if (desktopBadge) {
                    desktopBadge.innerText = status.toUpperCase();
                    updateStatusClasses(desktopBadge, status);
                }

                // Mobile
                const mobileRow = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .status-badge`
                );
                const mobileIcon = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .olt-status-indicator`
                );

                if (mobileRow) {
                    mobileRow.innerText = status.toUpperCase();
                    updateStatusClasses(mobileRow, status);
                }
                if (mobileIcon) {
                    mobileIcon.innerHTML = iconHtml;
                    updateStatusClasses(mobileIcon, status);
                }

                // Update ONU total
                if (updateTotalOnu) {
                    const totalOnuDesktop = document.querySelector(
                        `.olt-row-desktop[data-olt-id="${oltId}"] .onu-badge`
                    );
                    const totalOnuMobile = document.querySelector(
                        `.olt-row-mobile[data-olt-id="${oltId}"] .onu-badge`
                    );
                    if (totalOnuDesktop)
                        totalOnuDesktop.innerText = data.body.total_ont;
                    if (totalOnuMobile)
                        totalOnuMobile.innerText = data.body.total_ont;
                }

                // Update last scan
                const desktopTimeEl = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .last-scan-time`
                );
                const desktopDateEl = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .last-scan-date`
                );
                const mobileLastScanEl = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .last-scan`
                );

                if (desktopTimeEl) desktopTimeEl.innerText = lastTime;
                if (desktopDateEl) desktopDateEl.innerText = lastDate;
                if (mobileLastScanEl)
                    mobileLastScanEl.innerHTML = `<strong>Last Scan:</strong> ${lastDate} ${lastTime}`;

                // ✅ Optional success notification
                Swal.fire({
                    icon: "success",
                    title: "Scan Completed",
                    text: `${data.body?.olt_name || "Unknown OLT"} (${status})`,
                    timer: 1500,
                    showConfirmButton: false,
                });
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Failed to Scan",
                    text: err.message || "An unexpected error occurred.",
                });
            } finally {
                btn.innerHTML = originalHTML;
                btn.disabled = false;
            }
        });
    }

    // Apply to both types of buttons
    document
        .querySelectorAll(".scan-olt-btn")
        .forEach((btn) => handleOltAction(btn, true));
    document
        .querySelectorAll(".check-snmp-status")
        .forEach((btn) => handleOltAction(btn, false));

    document
        .getElementById("batch-scan")
        .addEventListener("click", async () => {
            const url = `/cpanel/network/oltmanagements/scan/start`;
            let res = await fetch(url, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    Accept: "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify({}), // 👈 penting, jangan kosong
            });

            setProgressBar();

            if (!res.ok) {
                const errorText = await res.text(); // ambil teks error dari response
                Swal.fire({
                    icon: "error",
                    title: `Request failed (${res.status})`,
                    text:
                        errorText ||
                        "An error occurred while processing the request",
                });
                return;
            }

            let data = await res.json();
            startPolling(data.job_id);
        });

    // 🔹 helper untuk update status row OLT
    function updateOltRow({
        olt_id,
        status,
        ont_count,
        last_scan_time,
        last_scan_date,
    }) {
        const lowerStatus = (status || "").toLowerCase();

        // 🔹 Desktop: status-cell, last-scan-time/date, total-onus
        document
            .querySelectorAll(`.olt-row[data-olt-id="${olt_id}"]`)
            .forEach((row) => {
                // Status
                const statusCell = row.querySelector(".status-cell");
                if (statusCell) {
                    let icon = "";
                    switch (lowerStatus) {
                        case "active":
                            icon =
                                '<i class="fas fa-check-circle text-success"></i>';
                            break;
                        case "inactive":
                            icon =
                                '<i class="fas fa-times-circle text-danger"></i>';
                            break;
                        case "scanning":
                            icon =
                                '<i class="fas fa-circle-notch fa-spin text-warning"></i>';
                            break;
                        default:
                            icon =
                                '<i class="fas fa-question-circle text-secondary"></i>';
                    }
                    const wrapper = row.querySelector(".olt-status-wrapper");
                    if (wrapper) {
                        renderOltStatus(wrapper, status);
                    }
                }

                // Last scan
                const lastScanTime = row.querySelector(".last-scan-time");
                if (lastScanTime)
                    lastScanTime.innerHTML = `<span class="last-scan-time" title="${last_scan_date}">${last_scan_time}</span>`;
                const lastScanDate = row.querySelector(".last-scan-date");
                if (lastScanDate)
                    lastScanDate.innerHTML = `<span class="last-scan-date">${last_scan_date}</span>`;

                // Total ONUs
                const totalOnus = row.querySelector(".total-onus");
                if (totalOnus)
                    totalOnus.innerHTML = `<td><span class="badge onu-badge">${ont_count}</span></td>`;
            });

        // 🔹 Mobile card structure: .card-body[data-olt-id]
        document
            .querySelectorAll(`.card-body[data-olt-id="${olt_id}"]`)
            .forEach((card) => {
                // Status
                const statusWrapper = card.querySelector(
                    ".olt-status-indicator-wrapper"
                );
                if (statusWrapper) {
                    const icon = (() => {
                        switch (lowerStatus) {
                            case "active":
                                return '<i class="fas fa-check-circle text-success"></i>';
                            case "inactive":
                                return '<i class="fas fa-times-circle text-danger"></i>';
                            case "scanning":
                                return '<i class="fas fa-circle-notch fa-spin text-warning"></i>';
                            default:
                                return '<i class="fas fa-question-circle text-secondary"></i>';
                        }
                    })();
                    statusWrapper.innerHTML = `
                <div class="olt-status-indicator ${lowerStatus}">${icon}</div>
                <span class="status-badge ${lowerStatus}">${status.toUpperCase()}</span>
            `;
                }

                // Total ONUs
                const totalOnus = card.querySelector(".total-onus");
                if (totalOnus)
                    totalOnus.innerHTML = `<strong>{{ __('networks.table.onu') }}:</strong> ${ont_count}`;

                // Last scan
                const lastScan = card.querySelector(".last-scan");
                if (lastScan)
                    lastScan.innerHTML = `<strong>{{ __('networks.table.last-scan') }}:</strong> ${last_scan_date} ${last_scan_time}`;
            });
    }

    function padZero(num) {
        return num.toString().padStart(2, "0");
    }

    function updateCurrentTimeElements() {
        const now = new Date();

        // last-check -> h:i:s AM/PM
        let lastCheck = document.getElementById("last-check");
        if (lastCheck) {
            let hours = now.getHours();
            let minutes = padZero(now.getMinutes());
            let seconds = padZero(now.getSeconds());
            let ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;
            lastCheck.innerText = `${hours}:${minutes}:${seconds} ${ampm}`;
        }

        // current-time -> Month day, Year at h:i AM/PM
        let currentTime = document.getElementById("current-time");
        if (currentTime) {
            const monthNames = [
                "January",
                "February",
                "March",
                "April",
                "May",
                "June",
                "July",
                "August",
                "September",
                "October",
                "November",
                "December",
            ];
            let month = monthNames[now.getMonth()];
            let day = padZero(now.getDate());
            let year = now.getFullYear();

            let hours = now.getHours();
            let minutes = padZero(now.getMinutes());
            let ampm = hours >= 12 ? "PM" : "AM";
            hours = hours % 12 || 12;

            currentTime.innerText = `${month} ${day}, ${year} at ${hours}:${minutes} ${ampm}`;
        }
    }

    async function startPolling(jobId) {
        let lastCurrent = 0; // 🔹 simpan state terakhir

        let interval = setInterval(async () => {
            try {
                let r = await fetch(
                    `/cpanel/network/oltmanagements/scan/status/${jobId}`
                );
                let s = await r.json();

                // 🔹 Interpolasi: isi gap kalau current lompat
                let targetCurrent = s.current ?? 0;

                if (targetCurrent > lastCurrent + 1) {
                    // kalau lompat (misal 0 → 2), isi dulu 1
                    for (let i = lastCurrent + 1; i <= targetCurrent; i++) {
                        updateProgressUI(i, s); // bikin fungsi khusus untuk update UI
                    }
                } else {
                    updateProgressUI(targetCurrent, s);
                }

                lastCurrent = targetCurrent; // simpan posisi terakhir

                // kalau sudah selesai
                if (s.status === "done" || s.status === "not_found") {
                    clearInterval(interval);
                    updateCurrentTimeElements();

                    if (s.status === "done") {
                        await fetch(
                            `/cpanel/network/oltmanagements/${jobId}/clear`
                        );
                        let currentOltEl = document.getElementById(
                            "batch-check-current-olt"
                        );
                        if (currentOltEl)
                            currentOltEl.innerText =
                                "{{ __('networks.label.check-completed') }}";

                        let progressContainer = document.getElementById(
                            "batch-check-progress-container"
                        );
                        if (progressContainer)
                            progressContainer.style.display = "none";

                        let scanningValue =
                            document.getElementById("scanning-count");
                        if (scanningValue) scanningValue.innerText = 0;
                    }
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "❌ Polling Error",
                    text:
                        err.message ||
                        "An error occurred while retrieving OLT data",
                });
                clearInterval(interval);
                updateCurrentTimeElements();
            }
        }, 250);
    }

    // 🔹 pisahkan fungsi update UI supaya gampang dipanggil step-by-step
    function updateProgressUI(current, s) {
        // progress bar global
        let currentEl = document.getElementById("batch-check-current");
        if (currentEl) currentEl.innerText = current ?? "-";

        let totalEl = document.getElementById("batch-check-total");
        if (totalEl) totalEl.innerText = s.total ?? "-";

        let percent = s.total > 0 ? Math.round((current / s.total) * 100) : 0;

        let percentEl = document.getElementById("batch-check-percentage");
        if (percentEl) percentEl.innerText = percent + "%";

        let bar = document.getElementById("batch-check-progress-bar");
        if (bar) bar.style.width = percent + "%";

        let currentOltEl = document.getElementById("batch-check-current-olt");
        if (currentOltEl) currentOltEl.innerText = s.currentOlt ?? "-";

        // 🔹 update jumlah OLT yang masih scanning
        let scanningValue = document.getElementById("scanning-count");
        if (scanningValue) {
            let scanningCount = (s.total ?? 0) - (current ?? 0);
            scanningValue.innerText = scanningCount > 0 ? scanningCount : 0;
        }

        // 🔹 update online OLTs
        if (s.onlineOlts !== undefined) {
            let onlineEl = document.getElementById("online-olts");
            if (onlineEl) onlineEl.innerText = s.onlineOlts;

            let bar = document.getElementById("online-olts-bar");
            if (bar) {
                let percentOnline =
                    s.total > 0 ? (s.onlineOlts / s.total) * 100 : 0;
                bar.style.width = percentOnline + "%";
            }
        }

        // 🔹 update total ONUs
        if (s.totalOnus !== undefined) {
            let totalOnusEl = document.getElementById("total-onus");
            if (totalOnusEl) totalOnusEl.innerText = s.totalOnus;
        }

        // 🔹 update row spesifik OLT
        const oltsArray =
            s.olts && typeof s.olts === "object" ? Object.values(s.olts) : [];
        oltsArray.forEach(updateOltRow);
    }

    document
        .getElementById("rescan-olts")
        .addEventListener("click", async () => {
            let form = document.getElementById("filter-form");
            let formData = new FormData(form);

            // Convert FormData → object biasa
            let filters = {};
            formData.forEach((value, key) => {
                if (value && value !== "0") {
                    // skip kosong & status=0 (all)
                    filters[key] = value;
                }
            });

            setProgressBar();

            let res = await fetch(window.rescanUrl, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                },
                body: JSON.stringify(filters), // kirim filter yang aktif saja
            });

            let data = {};
            try {
                data = await res.json();
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "❌ JSON Parse Error",
                    text:
                        err.message ||
                        "An error occurred while reading the JSON response.",
                });
                return; // hentikan eksekusi jika JSON gagal di-parse
            }

            if (data.job_id) {
                startPolling(data.job_id);
            } else {
                Swal.fire({
                    icon: "warning",
                    title: "⚠️ jobId Not Founded",
                    text:
                        "Response does not have jobId: " + JSON.stringify(data),
                });
            }
        });

    function confirmDelete(oltId, oltName, deleteUrl) {
        Swal.fire({
            title: `Delete OLT "${oltName}"?`,
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                fetch(deleteUrl, {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        Accept: "application/json",
                    },
                })
                    .then(async (res) => {
                        const data = await res.json();
                        return data;
                    })
                    .then((data) => {
                        if (data.success) {
                            Swal.fire(
                                "Deleted!",
                                `OLT "${oltName}" has been deleted.`,
                                "success"
                            );
                            const row = document.querySelector(
                                `tr[data-olt-id='${oltId}']`
                            );
                            if (row) row.remove();

                            let onlineEl =
                                document.getElementById("online-olts");
                            if (onlineEl) onlineEl.innerText = data.onlineOlts;

                            let totalOnusEl =
                                document.getElementById("total-onus");
                            if (totalOnusEl)
                                totalOnusEl.innerText = data.totalOnus;

                            let totalEl = document.getElementById("total-olts");
                            if (totalEl) totalEl.innerText = data.totalOlts;
                        } else {
                            Swal.fire(
                                "Error",
                                data.error || "Failed to delete.",
                                "error"
                            );
                        }
                    })
                    .catch((err) => {
                        Swal.fire("Error", "Something went wrong.", "error");
                    });
            }
        });
    }

    function setProgressBar(totalOlts = 0) {
        const progressContainer = document.getElementById(
            "batch-check-progress-container"
        );
        if (progressContainer) {
            progressContainer.classList.remove("d-none");
            progressContainer.style.display = "block";
        }

        const currentEl = document.getElementById("batch-check-current");
        const totalEl = document.getElementById("batch-check-total");
        const percentEl = document.getElementById("batch-check-percentage");
        const bar = document.getElementById("batch-check-progress-bar");
        const currentOltEl = document.getElementById("batch-check-current-olt");

        if (currentEl) currentEl.innerText = 0;
        if (totalEl) totalEl.innerText = totalOlts;
        if (percentEl) percentEl.innerText = "0%";
        if (bar) bar.style.width = "0%";
        if (currentOltEl) currentOltEl.innerText = "Starting...";
    }

    document
        .getElementById("batch-status-check")
        ?.addEventListener("click", async function (e) {
            e.preventDefault();

            const btn = this;
            btn.disabled = true;
            const originalText = btn.innerHTML;
            btn.innerHTML =
                '<i class="fas fa-sync-alt fa-spin me-1"></i> Checking...';

            // hitung total OLT visible di tabel
            const oltRows = Array.from(document.querySelectorAll(".olt-row"));
            const totalOlts = oltRows.length;

            // update kartu statistik & progress awal
            const scanningCount = document.getElementById("scanning-count");
            if (scanningCount) scanningCount.innerText = totalOlts;
            setProgressBar(totalOlts);

            const url = `/cpanel/network/oltmanagements/check-all-status`;

            try {
                const res = await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document
                            .querySelector('meta[name="csrf-token"]')
                            .getAttribute("content"),
                        Accept: "application/json",
                    },
                });

                if (!res.ok) throw new Error("Failed to dispatch batch job");

                const data = await res.json();

                if (!data.job_id) throw new Error("Missing job ID in response");

                const jobId = data.job_id;

                // simpan ke window biar bisa diakses global
                window.currentJobId = jobId;

                // mulai polling progress spesifik untuk job ini
                startPolling(jobId);
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "❌ Error Dispatching Job",
                    text: err.message || "Failed to start check all status",
                });
            } finally {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
});
