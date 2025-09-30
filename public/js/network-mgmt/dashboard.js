document.addEventListener("DOMContentLoaded", () => {
    let jobId = document.getElementById("scan-job-id")?.value;
    if (jobId) startPolling(jobId);

    const filterForm = document.getElementById("filter-form");

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
});

document.getElementById("batch-scan").addEventListener("click", async () => {
    let res = await fetch(window.rescanUrl, {
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
            text: errorText || "An error occurred while processing the request",
        });
        return;
    }

    let data = await res.json();
    startPolling(data.jobId);
});

// 🔹 helper untuk update status row OLT
function updateOltRow({
    olt_id,
    status,
    onu_count,
    last_scan_time,
    last_scan_date,
}) {
    // 1. update status cell di desktop & mobile
    document
        .querySelectorAll(`.olt-row[data-olt-id="${olt_id}"] .status-cell`)
        .forEach((cell) => {
            let icon = "";
            switch ((status || "").toLowerCase()) {
                case "active":
                    icon = '<i class="fas fa-check-circle text-success"></i>';
                    break;
                case "inactive":
                    icon = '<i class="fas fa-times-circle text-danger"></i>';
                    break;
                case "scanning":
                    icon =
                        '<i class="fas fa-circle-notch fa-spin text-warning"></i>';
                    break;
                default:
                    icon =
                        '<i class="fas fa-question-circle text-secondary"></i>';
            }

            cell.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="olt-status-indicator ${status.toLowerCase()}">
                        ${icon}
                    </div>
                    <span class="badge status-badge ${status.toLowerCase()}">
                        ${status.toUpperCase()}
                    </span>
                </div>
            `;
        });

    // 2. update last scan time
    document
        .querySelectorAll(`.olt-row[data-olt-id="${olt_id}"] .last-scan-time`)
        .forEach((el) => {
            el.innerHTML = `
                <span class="last-scan-time" title="${last_scan_date}">
                    ${last_scan_time}
                </span>
            `;
        });

    // 3. update last scan date
    document
        .querySelectorAll(`.olt-row[data-olt-id="${olt_id}"] .last-scan-date`)
        .forEach((el) => {
            el.innerHTML = `
                <span class="last-scan-date">${last_scan_date}</span>
            `;
        });

    document
        .querySelectorAll(`.olt-row[data-olt-id="${olt_id}"] .total-onus`)
        .forEach((el) => {
            el.innerHTML = `<td><span class="badge onu-badge">${onu_count}</span>
        </td>`;
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
                    console.log("current: ", s.current);
                    console.log("Status: ", s.status);
                    console.log("Online Olts: ", s.onlineOlts);
                    updateProgressUI(i, s); // bikin fungsi khusus untuk update UI
                }
            } else {
                console.log("current: ", s.current);
                console.log("Status: ", s.status);
                console.log("Online Olts: ", s.onlineOlts);
                updateProgressUI(targetCurrent, s);
            }

            lastCurrent = targetCurrent; // simpan posisi terakhir

            // kalau sudah selesai
            if (s.status === "done" || s.status === "not_found") {
                clearInterval(interval);
                updateCurrentTimeElements();

                if (s.status === "done") {
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

                // 🔹 Hapus cache backend lewat endpoint khusus
                const url = window.clearCacheUrl.replace(":jobId", jobId);

                await fetch(url, {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                });
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
    if (s.lastResult) {
        updateOltRow(s.lastResult);
    }
}

document.getElementById("rescan-olts").addEventListener("click", async () => {
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

    if (data.jobId) {
        startPolling(data.jobId);
    } else {
        Swal.fire({
            icon: "warning",
            title: "⚠️ jobId Not Founded",
            text: "Response does not have jobId: " + JSON.stringify(data),
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

                        let onlineEl = document.getElementById("online-olts");
                        if (onlineEl) onlineEl.innerText = data.onlineOlts;

                        let totalOnusEl = document.getElementById("total-onus");
                        if (totalOnusEl) totalOnusEl.innerText = data.totalOnus;

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
    if (progressContainer) progressContainer.style.display = "block";

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

        const oltRows = Array.from(document.querySelectorAll(".olt-row"));
        const totalOlts = oltRows.length;

        setProgressBar(totalOlts);

        try {
            const res = await fetch(window.checkAllOltStatus, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
            });

            const data = await res.json();

            // mulai polling dengan totalOlts
            startOltStatusPolling(totalOlts);
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

async function startOltStatusPolling(totalOlts) {
    const interval = setInterval(async () => {
        try {
            const res = await fetch(window.oltStatusProgressRoute);

            if (!res.ok) {
                Swal.fire({
                    icon: "error",
                    title: `❌ Fetch Failed`,
                    text: `Request failed with status: ${res.status}`,
                });
                return;
            }

            const data = await res.json();

            // jumlah OLT yang selesai
            let completedCount = data.current ?? 0;
            const percent = Math.round((completedCount / totalOlts) * 100);

            // update progress bar
            document.getElementById("batch-check-current").innerText =
                completedCount;
            document.getElementById("batch-check-total").innerText = totalOlts;
            document.getElementById("batch-check-percentage").innerText =
                percent + "%";
            document.getElementById("batch-check-progress-bar").style.width =
                percent + "%";

            // update OLT rows
            for (let oltId in data.statuses) {
                const oltEntry = data.statuses[oltId];
                const status = oltEntry.status; // <-- ambil statusnya
                const oltName = oltEntry.name; // <-- ambil nama OLT

                const iconHtml = {
                    Active: '<i class="fas fa-check-circle text-success"></i>',
                    Inactive: '<i class="fas fa-times-circle text-danger"></i>',
                    Scanning: '<i class="fas fa-sync-alt text-warning"></i>',
                }[status];

                const desktopRow = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .olt-status-indicator`
                );
                const desktopBadge = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .status-badge`
                );
                if (desktopRow) desktopRow.innerHTML = iconHtml;
                if (desktopBadge) desktopBadge.innerText = status;

                const mobileRow = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .status-badge`
                );
                const mobileIcon = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .olt-status-indicator`
                );
                if (mobileRow) mobileRow.innerText = status;
                if (mobileIcon) mobileIcon.innerHTML = iconHtml;
            }

            // update teks OLT yang sedang dicek
            const nextOltId = Object.keys(data.statuses).find(
                (id) => data.statuses[id].status === "Scanning"
            );
            const nextOltName = nextOltId ? data.statuses[nextOltId].name : "-";
            const nextOltEl = nextOltId
                ? document.querySelector(`.olt-row[data-olt-id="${nextOltId}"]`)
                : null;

            if (completedCount === totalOlts) {
                clearInterval(interval);

                document.getElementById("batch-check-current-olt").innerText =
                    "Done";
                document.getElementById(
                    "batch-check-progress-container"
                ).style.display = "none";
            } else {
                document.getElementById(
                    "batch-check-current-olt"
                ).innerText = `Checking ${nextOltName} (${completedCount} of ${totalOlts})`;
            }
        } catch (err) {
            Swal.fire({
                icon: "error",
                title: "❌ Polling Error",
                text: err.message || "An error occurred while polling the job.",
            });
        }
    }, 2000);
}

document.addEventListener("DOMContentLoaded", () => {
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
                            err.message ||
                            "An error occurred while reading the JSON response.",
                    });
                }

                const oltId = btn.dataset.oltId;
                const status = data.status;

                const iconHtml = {
                    Active: '<i class="fas fa-check-circle text-success"></i>',
                    Inactive: '<i class="fas fa-times-circle text-danger"></i>',
                    Scanning: '<i class="fas fa-sync-alt text-warning"></i>',
                }[status];

                // Update desktop row
                const desktopRow = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .olt-status-indicator`
                );
                const desktopBadge = document.querySelector(
                    `.olt-row-desktop[data-olt-id="${oltId}"] .status-cell .status-badge`
                );
                if (desktopRow) desktopRow.innerHTML = iconHtml;
                if (desktopBadge) desktopBadge.innerText = status;

                // Update mobile row
                const mobileRow = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .status-badge`
                );
                const mobileIcon = document.querySelector(
                    `.olt-row-mobile[data-olt-id="${oltId}"] .olt-status-indicator`
                );
                if (mobileRow) mobileRow.innerText = status;
                if (mobileIcon) mobileIcon.innerHTML = iconHtml;

                // Update total ONU jika perlu
                if (updateTotalOnu) {
                    const totalOnuBadge = document.querySelector(
                        `.olt-row-desktop[data-olt-id="${oltId}"] .onu-badge`
                    );
                    const totalOnuMobileBadge = document.querySelector(
                        `.olt-row-mobile[data-olt-id="${oltId}"] .onu-badge`
                    );
                    if (totalOnuBadge) totalOnuBadge.innerText = data.total_onu;
                    if (totalOnuMobileBadge)
                        totalOnuMobileBadge.innerText = data.total_onu;
                }
            } catch (err) {
                Swal.fire({
                    icon: "error",
                    title: "Failed to Scan",
                    text: err.message,
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
});
