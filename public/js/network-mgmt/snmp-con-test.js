// SNMP Test functionality
document.getElementById("test-snmp").addEventListener("click", function () {
    const olt_name = document.getElementById("olt_name").value;
    const ip_address = document.getElementById("ip_address").value;
    const port = document.getElementById("port").value;
    const community = document.getElementById("community").value;
    const olt_type = document.getElementById("olt_type").value;

    if (!ip_address || !port || !community || !olt_type) {
        Swal.fire({
            icon: "warning",
            title: "Incomplete Form",
            text: "Please fill in all required fields before testing SNMP connection.",
            confirmButtonColor: "#3085d6",
            confirmButtonText: "OK",
        });
        return;
    }

    const resultDiv = document.getElementById("snmp-test-result");
    resultDiv.innerHTML =
        '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-3">Testing SNMP connection...</p></div>';

    const modal = new bootstrap.Modal(document.getElementById("snmpTestModal"));
    modal.show();

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 30 * 1000);

    const url = `/cpanel/network/oltmanagements/SNMPConnection`;

    fetch(url, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({
            olt_name,
            ip_address,
            port,
            community,
            olt_type,
        }),
        signal: controller.signal,
    })
        .then((response) => {
            return response.json();
        })
        .then((data) => {
            clearTimeout(timeout);

            if (data.success) {
                resultDiv.innerHTML = `
                    <div class="alert alert-success">
                        <h5 class="d-flex align-items-center"><i class="fas fa-check-circle me-2 mr-1"></i> Connection Successful!</h5>
                        <hr>
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-success me-2"><i class="fas fa-wifi"></i></span>
                            <span class="ml-1">Connection to <strong>${ip_address}:${port}</strong> is working</span>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <div class="card p-2 mb-0">
                                    <small class="text-muted">Device Info</small>
                                    <div class="fw-bold">${
                                        data.device_info || "Not available"
                                    }</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card p-2 mb-0">
                                    <small class="text-muted">ONT Count</small>
                                    <div class="fw-bold">${
                                        typeof data.ont_count === "number"
                                            ? data.ont_count
                                            : "Unknown"
                                    }</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else {
                resultDiv.innerHTML = `
                    <div class="alert alert-danger">
                        <h5 class="d-flex align-items-center"><i class="fas fa-exclamation-triangle me-2 mr-1"></i> Connection Failed!</h5>
                        <hr>
                        <p>${data.error}</p>
                    </div>
                `;
            }
        })
        .catch((error) => {
            clearTimeout(timeout);

            let errorMessage =
                error.name === "AbortError"
                    ? "Request timed out. The device did not respond in time."
                    : error.message;

            resultDiv.innerHTML = `
                <div class="alert alert-danger">
                    <h5 class="d-flex align-items-center"><i class="fas fa-exclamation-circle me-2 mr-1"></i> Error!</h5>
                    <hr>
                    <p>${errorMessage}</p>
                </div>
            `;
        });
});
