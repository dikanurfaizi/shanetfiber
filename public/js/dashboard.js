// resources/js/dashboard.js

// Chart untuk Ringkasan Bulanan
let chart;

async function fetchMonthlySummary() {
    try {
        const apiUrl = window.apiMonthlySummaryUrl;
        const response = await fetch(apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        });

        const data = (await response.json()) || {};
        const { billingSummary = [], permissions = [] } = data ?? {};

        if (!permissions.length) {
            document.querySelector("#chartWrapper1").innerHTML =
                "<div class='text-center p-4 text-muted'>No data available</div>";
            return;
        }

        const months = billingSummary.map((item) => item.month_name || "");
        const totalPaid = billingSummary.map((item) => item.total_paid || 0);
        const totalUnpaid = billingSummary.map(
            (item) => item.total_unpaid || 0
        );
        const totalPaylater = billingSummary.map(
            (item) => item.total_paylater || 0
        );
        const totalCustomers = billingSummary.map(
            (item) => item.total_customers || 0
        );

        let series = [];

        if (
            permissions.includes("view-billings-chart-nominal-total") ||
            permissions.includes("view-billings-chart-nominal-related")
        ) {
            series = [
                {
                    name: "Unpaid (Rp)",
                    data: totalUnpaid,
                    color: "#dc3545",
                },
                {
                    name: "Paid (Rp)",
                    data: totalPaid,
                    color: "#28a745",
                },
            ];
        } else if (permissions.includes("view-billings-chart-customer-count")) {
            series = [
                {
                    name: "Unpaid",
                    data: totalUnpaid,
                    color: "#dc3545",
                },
                {
                    name: "Paylater",
                    data: totalPaylater,
                    color: "#ffc107",
                },
                {
                    name: "Paid",
                    data: totalPaid,
                    color: "#28a745",
                },
            ];
        }

        const yAxisMax =
            Math.max(...totalPaid, ...totalUnpaid, ...totalCustomers) * 1.25;

        const options = {
            chart: {
                type: "bar",
                height: 350,
                stacked: true,
                toolbar: {
                    show: false,
                },
            },
            title: {
                text: "Monthly Customer Summary",
                align: "center",
                style: {
                    fontSize: "18px",
                    fontWeight: "bold",
                    color: "#333",
                },
            },
            series,
            xaxis: {
                categories: months,
                labels: {
                    rotate: -45,
                },
            },
            yaxis: {
                min: 0,
                max: yAxisMax,
                labels: {
                    formatter: (value) =>
                        permissions.includes(
                            "view-billings-chart-nominal-total"
                        ) ||
                        permissions.includes(
                            "view-billings-chart-nominal-related"
                        )
                            ? "Rp " + value.toLocaleString()
                            : `${Math.round(value)} customers`,
                },
            },
            tooltip: {
                y: {
                    formatter: (value) =>
                        permissions.includes(
                            "view-billings-chart-nominal-total"
                        ) ||
                        permissions.includes(
                            "view-billings-chart-nominal-related"
                        )
                            ? "Rp " + value.toLocaleString()
                            : `${Math.round(value)} customers`,
                },
            },
            dataLabels: {
                enabled: false,
            },
            legend: {
                position: "top",
                horizontalAlign: "center",
            },
        };

        if (chart) chart.destroy();
        chart = new ApexCharts(
            document.querySelector("#chartWrapper1"),
            options
        );
        chart.render();
    } catch (error) {
        console.error("Error fetching data: ", error);
    }
}

// Trigger update cache tiap 20 detik
function scheduleTriggerCache(routerSlug) {
    setInterval(() => {
        triggerUpdateCache(routerSlug);
    }, 20000); // tiap 20 detik
}

function triggerUpdateCache(routerSlug) {
    $.post(
        window.routes.updateCache.replace("__ROUTER__", routerSlug),
        {
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        function (res) {
            // console.log("[CACHE UPDATED]", res);
        }
    ).fail(function (err) {
        // console.error("[CACHE UPDATE ERROR]", err);
    });
}

// polling untuk ambil data dari cache
function pollResource(routerSlug) {
    setInterval(() => {
        $.get(
            window.routes.getResource.replace("__ROUTER__", routerSlug),
            function (res) {
                // console.log("[CACHE DATA]", res);
                if (res.resources?.length > 0) {
                    updateSystemResources(routerSlug, res.resources[0]);
                }
            }
        ).fail(function (err) {
            // console.error("[CACHE ERROR]", err);
        });
    }, 10000); // tiap 10 detik
}

function updateSystemResources(routerSlug, r) {
    const memoryUse = r.totalMemory - r.freeMemory;
    const memoryPercent = Math.round((memoryUse / r.totalMemory) * 100);

    const hddUse = r.totalHdd - r.freeHdd;
    const hddPercent = Math.round((hddUse / r.totalHdd) * 100);

    // CPU
    $("#cpu-bar-" + routerSlug).css("width", r.cpuLoad + "%");
    $("#cpu-" + routerSlug).text(r.cpuLoad + "%");

    // RAM
    $("#ram-bar-" + routerSlug).css("width", memoryPercent + "%");
    $("#memory-" + routerSlug).text(r.memoryUsage + "/" + r.totalMemorySpace);

    // HDD
    $("#hdd-bar-" + routerSlug).css("width", hddPercent + "%");
    $("#hdd-" + routerSlug).text(r.hddUsage + "/" + r.totalHddSpace);

    // Board & uptime
    $("#boardname-" + routerSlug).text(r.boardName);
    $("#uptime-" + routerSlug).text(r.uptime);

    // Status
    $("#status-" + routerSlug)
        .removeClass("text-danger")
        .addClass("text-success");
}

// init
window.mikrotiks.forEach((router) => {
    triggerUpdateCache(router.slug); // langsung trigger sekali saat load
    scheduleTriggerCache(router.slug); // rutin trigger tiap 20 detik
    pollResource(router.slug); // rutin ambil cache tiap 10 detik
});

// // Fungsi untuk Router Status
// function initializeRouterStatus() {
//     $.ajaxSetup({
//         headers: {
//             "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
//         },
//     });

//     // Event click check online
//     $(document).on("click", "#check-online", function (e) {
//         e.preventDefault();
//         const selectedRouterId = $(this).data("id");

//         const url = window.routes.checkOnline.replace(
//             "__ID__",
//             selectedRouterId
//         );

//         console.log(
//             "[CHECK ONLINE] Triggered for router:",
//             selectedRouterId,
//             "URL:",
//             url
//         );

//         $.ajax({
//             url: url,
//             method: "POST",
//             data: { id: selectedRouterId },
//             success: function (response) {
//                 console.log("[CHECK ONLINE SUCCESS]", response);

//                 new Notify({
//                     status: response.status,
//                     title: response.title,
//                     text: response.message,
//                     effect: "slide",
//                     speed: 500,
//                     showCloseButton: true,
//                     autotimeout: 5000,
//                     autoclose: true,
//                 });
//             },
//             error: function (data) {
//                 console.error("[CHECK ONLINE ERROR]", data);
//                 new Notify({
//                     status: "error",
//                     title: "Connection failed!",
//                     text: data.responseJSON?.message || "An error occurred",
//                     effect: "slide",
//                     speed: 500,
//                     showCloseButton: true,
//                     autotimeout: 5000,
//                     autoclose: true,
//                 });
//             },
//         });
//     });

//     // Router status update
//     function updateRouterData(routerSlug) {
//         console.log("[INIT ROUTER DATA]", routerSlug);

//         $("#loading_ppp_secret-" + routerSlug).removeClass("d-none");
//         $("#loading_ppp_non_active-" + routerSlug).removeClass("d-none");
//         $("#loading_ppp_active-" + routerSlug).removeClass("d-none");

//         let allPppSecretsCount = 0;
//         let allPppActiveCount = 0;

//         function updateCountPppSecret() {
//             console.log(`[REQ] Get PPP Secrets for ${routerSlug}`);
//             return $.ajax({
//                 url: window.routes.getPppSecrets.replace(
//                     "__ROUTER__",
//                     routerSlug
//                 ),
//                 method: "GET",
//                 success: function (response) {
//                     console.log(
//                         `[RES] PPP Secrets for ${routerSlug}`,
//                         response
//                     );
//                     allPppSecretsCount = response.allPppSecretsCount;
//                     $("#ppp-secrets-count-" + routerSlug).text(
//                         allPppSecretsCount
//                     );
//                 },
//                 error: function (err) {
//                     console.error(`[ERR] PPP Secrets for ${routerSlug}`, err);
//                 },
//             });
//         }

//         function updateCountPppActive() {
//             console.log(`[REQ] Get PPP Active for ${routerSlug}`);
//             return $.ajax({
//                 url: window.routes.getPppActive.replace(
//                     "__ROUTER__",
//                     routerSlug
//                 ),
//                 method: "GET",
//                 success: function (response) {
//                     console.log(`[RES] PPP Active for ${routerSlug}`, response);
//                     allPppActiveCount = response.allPppActiveCount;
//                     $("#ppp-secrets-active-count-" + routerSlug).text(
//                         allPppActiveCount
//                     );
//                 },
//                 error: function (err) {
//                     console.error(`[ERR] PPP Active for ${routerSlug}`, err);
//                 },
//             });
//         }

//         function updateCountPppNonActive() {
//             const total = allPppSecretsCount - allPppActiveCount;
//             console.log(`[CALC] Non Active for ${routerSlug} = ${total}`);
//             $("#ppp-secrets-non-active-count-" + routerSlug).text(total);
//         }

//         function updateSystemResources() {
//             console.log(`[REQ] Get Resources for ${routerSlug}`);
//             return $.ajax({
//                 url: window.routes.mikrotikResources.replace(
//                     "__ROUTER__",
//                     routerSlug
//                 ),
//                 method: "GET",
//                 success: function (response) {
//                     console.log(`[RES] Resources for ${routerSlug}`, response);

//                     if (response.resources?.length > 0) {
//                         const r = response.resources[0];
//                         const memoryUse = r.totalMemory - r.freeMemory;
//                         const memoryPercent = Math.round(
//                             (memoryUse / r.totalMemory) * 100
//                         );
//                         const hddUse = r.totalHdd - r.freeHdd;
//                         const hddPercent = Math.round(
//                             (hddUse / r.totalHdd) * 100
//                         );

//                         $("#cpu-bar-" + routerSlug).css(
//                             "width",
//                             r.cpuLoad + "%"
//                         );
//                         $("#cpu-" + routerSlug).text(r.cpuLoad + "%");

//                         $("#ram-bar-" + routerSlug).css(
//                             "width",
//                             memoryPercent + "%"
//                         );
//                         $("#memory-" + routerSlug).text(
//                             r.memoryUsage + "/" + r.totalMemorySpace
//                         );

//                         $("#hdd-bar-" + routerSlug).css(
//                             "width",
//                             hddPercent + "%"
//                         );
//                         $("#hdd-" + routerSlug).text(
//                             r.hddUsage + "/" + r.totalHddSpace
//                         );

//                         $("#boardname-" + routerSlug).text(r.boardName);
//                         $("#uptime-" + routerSlug).text(r.uptime);
//                         $("#status-" + routerSlug)
//                             .removeClass("text-danger")
//                             .addClass("text-success");
//                     }
//                 },
//                 error: function (err) {
//                     console.error(`[ERR] Resources for ${routerSlug}`, err);
//                 },
//             });
//         }

//         async function updateData() {
//             console.log(`[START UPDATE DATA] ${routerSlug}`);
//             await updateCountPppActive();
//             await updateCountPppSecret();
//             updateCountPppNonActive();
//             await updateSystemResources();
//             console.log(`[DONE UPDATE DATA] ${routerSlug}`);
//         }

//         updateData().catch(console.error);

//         setInterval(updateSystemResources, 10000);
//         setInterval(updateCountPppNonActive, 5000);
//         setInterval(() => {
//             updateCountPppSecret();
//             updateCountPppActive();
//         }, 10000);

//         $(document).ajaxStop(function () {
//             console.log(`[AJAX COMPLETE] ${routerSlug}`);
//             $("#loading_ppp_secret-" + routerSlug).addClass("d-none");
//             $("#loading_ppp_non_active-" + routerSlug).addClass("d-none");
//             $("#loading_ppp_active-" + routerSlug).addClass("d-none");
//         });
//     }

//     // Init routers
//     if (window.mikrotiks && window.mikrotiks.length > 0) {
//         console.log("[INIT ONLINE ROUTERS]", window.mikrotiks);
//         window.mikrotiks.forEach(function (router) {
//             updateRouterData(router.slug);
//         });
//     }
// }

// Fungsi untuk Customer Registration & Churn Chart
let customerChart;
let resizeTimer;

async function fetchCustomerRegistrationChurnData() {
    try {
        const apiUrl = window.apifetchCustomerRegistrationUrl;

        const response = await fetch(apiUrl, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]'
                ).content,
                Accept: "application/json",
            },
            body: JSON.stringify({}),
        });

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Network response not OK:", {
                status: response.status,
                statusText: response.statusText,
                responseText: errorText,
            });
            throw new Error(`Network response was not ok: ${response.status}`);
        }

        const data = await response.json();
        if (data.error) {
            console.error("API returned error:", data.error);
            return;
        }

        renderCustomerRegistrationChurnChart(data);
    } catch (error) {
        console.error("Error in fetchCustomerRegistrationChurnData:", {
            errorName: error.name,
            errorMessage: error.message,
            errorStack: error.stack,
        });
    }
}

function renderCustomerRegistrationChurnChart(data) {
    try {
        const { months, registrationData, churnData, isSuperAdmin } = data;

        if (!months || !registrationData) {
            console.error("Invalid chart data:", { months, registrationData });
            return;
        }

        const series = [];
        const colors = [];

        if (isSuperAdmin) {
            Object.keys(registrationData).forEach((adminId, index) => {
                const adminName =
                    registrationData[adminId]?.adminName || `Admin ${adminId}`;
                const color = getColorForAdmin(index);

                series.push({
                    name: `${adminName} (Signup)`,
                    data: registrationData[adminId]?.counts || [],
                    type: "line",
                });

                series.push({
                    name: `${adminName} (Unsubscribe)`,
                    data:
                        churnData[adminId]?.counts ||
                        Array(months.length).fill(0),
                    type: "line",
                });

                colors.push(color, color);
            });
        } else {
            const adminId = Object.keys(registrationData)[0];
            if (adminId) {
                series.push({
                    name: "Signup",
                    data: registrationData[adminId]?.counts || [],
                    type: "line",
                });
                series.push({
                    name: "Unsubscribe",
                    data:
                        churnData[adminId]?.counts ||
                        Array(months.length).fill(0),
                    type: "line",
                });
                colors.push("#4e73df", "#e74a3b");
            }
        }

        const options = {
            series: series,
            chart: {
                type: "line",
                height: "100%",
                toolbar: {
                    show: true,
                },
                animations: {
                    enabled: false,
                },
                zoom: {
                    enabled: true,
                },
            },
            colors: colors,
            stroke: {
                width: 3,
                curve: "smooth",
            },
            dataLabels: {
                enabled: false,
            },
            title: {
                text: "Monthly Customer Signup vs Unsubscribe Trend",
                align: "center",
                style: {
                    fontSize: "16px",
                },
            },
            xaxis: {
                categories: months.map((m) => m || ""),
                labels: {
                    rotate: -45,
                    formatter: function (value) {
                        try {
                            if (!value) return "";
                            const strValue = String(value).trim();
                            if (window.innerWidth < 768) {
                                const monthPart = strValue.split(" ")[0];
                                return monthPart
                                    ? monthPart.substring(0, 3)
                                    : "";
                            }
                            return strValue;
                        } catch (e) {
                            console.warn("Error formatting label:", e);
                            return "";
                        }
                    },
                },
            },
            yaxis: {
                title: {
                    text: "Number of Customers",
                },
                min: 0,
                forceNiceScale: true,
                labels: {
                    formatter: (val) => Math.floor(val),
                },
            },
            legend: {
                position: "bottom",
                horizontalAlign: "center",
                fontSize: "12px",
                itemMargin: {
                    horizontal: 10,
                },
                markers: {
                    radius: 5,
                },
            },
            grid: {
                borderColor: "#f1f1f1",
                padding: {
                    top: 20,
                    bottom: 40,
                },
            },
            tooltip: {
                y: {
                    formatter: (val) => `${val} customer`,
                },
            },
            responsive: [
                {
                    breakpoint: 600,
                    options: {
                        chart: {
                            height: 300,
                        },
                        xaxis: {
                            labels: {
                                rotate: -45,
                                style: {
                                    fontSize: "8px",
                                },
                            },
                        },
                        legend: {
                            fontSize: "10px",
                            itemMargin: {
                                horizontal: 5,
                            },
                        },
                    },
                },
            ],
        };

        const chartEl = document.getElementById(
            "customerRegistrationChurnChart"
        );
        if (!chartEl) {
            console.error("Chart container not found!");
            return;
        }

        if (window.customerChart) {
            window.customerChart.destroy();
        }

        chartEl.innerHTML = "";
        window.customerChart = new ApexCharts(chartEl, options);
        window.customerChart.render();
    } catch (error) {
        console.error("Failed to render chart:", error);
    }
}

function getColorForAdmin(index) {
    const colors = [
        "#4e73df",
        "#1cc88a",
        "#36b9cc",
        "#f6c23e",
        "#e74a3b",
        "#858796",
        "#5a5c69",
        "#3a3b45",
        "#2e59d9",
        "#17a673",
        "#2c9faf",
        "#dda20a",
        "#be2617",
        "#656773",
        "#42444e",
        "#24252e",
    ];
    return colors[index % colors.length];
}

// Fungsi untuk Popular Packages
let packageChart;

function initializePackageViewToggle() {
    const viewButtons = document.querySelectorAll("[data-view]");
    viewButtons.forEach((button) => {
        button.addEventListener("click", function () {
            viewButtons.forEach((btn) => btn.classList.remove("active"));
            this.classList.add("active");

            if (this.dataset.view === "chart") {
                document.getElementById("packageTableView").style.display =
                    "none";
                document.getElementById("packageChartView").style.display =
                    "block";

                if (!window.packageChart) {
                    renderPackageChart();
                }
            } else {
                document.getElementById("packageTableView").style.display =
                    "block";
                document.getElementById("packageChartView").style.display =
                    "none";
            }
        });
    });
}

function renderPackageChart() {
    const packageData = window.packageSummary;
    const categories = packageData.map((p) => p.paket_name);
    const seriesData = packageData.map((p) => p.total_customers);

    const options = {
        series: [
            {
                name: "Customers",
                data: seriesData,
            },
        ],
        chart: {
            type: "bar",
            height: "100%",
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: false,
                    zoom: false,
                    zoomin: false,
                    zoomout: false,
                    pan: false,
                    reset: true,
                },
            },
        },
        colors: ["#4e73df"],
        plotOptions: {
            bar: {
                borderRadius: 4,
                horizontal: true,
                distributed: false,
                barHeight: "80%",
            },
        },
        dataLabels: {
            enabled: true,
            formatter: function (val) {
                return val;
            },
            style: {
                fontSize: "12px",
                colors: ["#fff"],
            },
        },
        xaxis: {
            categories: categories,
            title: {
                text: "Number of Customers",
                style: {
                    fontSize: "12px",
                },
            },
            labels: {
                trim: true,
                hideOverlappingLabels: true,
            },
        },
        yaxis: {
            labels: {
                style: {
                    fontSize: "12px",
                },
            },
        },
        tooltip: {
            y: {
                formatter: function (val) {
                    return val + " customers";
                },
            },
        },
        responsive: [
            {
                breakpoint: 600,
                options: {
                    chart: {
                        height: 400,
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                        },
                    },
                },
            },
        ],
    };

    window.packageChart = new ApexCharts(
        document.querySelector("#popularPackagesChart"),
        options
    );
    window.packageChart.render();
}

// Inisialisasi semua fungsi saat dokumen siap
document.addEventListener("DOMContentLoaded", function () {
    // Monthly Summary Chart
    fetchMonthlySummary();
    setInterval(fetchMonthlySummary, 300000);

    // // Router Status
    // initializeRouterStatus();

    // Customer Registration & Churn Chart
    fetchCustomerRegistrationChurnData();
    setInterval(fetchCustomerRegistrationChurnData, 300000);

    // Customer Movement View Toggle
    const viewButtons = document.querySelectorAll("[data-view-type]");
    viewButtons.forEach((button) => {
        button.addEventListener("click", function () {
            viewButtons.forEach((btn) => btn.classList.remove("active"));
            this.classList.add("active");

            if (this.dataset.viewType === "table") {
                document.getElementById(
                    "customerMovementChartView"
                ).style.display = "none";
                document.getElementById(
                    "customerMovementTableView"
                ).style.display = "block";
            } else {
                document.getElementById(
                    "customerMovementChartView"
                ).style.display = "block";
                document.getElementById(
                    "customerMovementTableView"
                ).style.display = "none";
            }
        });
    });

    // Initialize DataTable if needed
    if ($.fn.DataTable) {
        $("#customerMovementTable").DataTable({
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            pageLength: 12,
            responsive: true,
            order: [[0, "desc"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search months...",
            },
        });
    }

    // Package View Toggle
    initializePackageViewToggle();

    // Resize handler untuk chart
    window.addEventListener("resize", () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.customerChart) {
                window.customerChart.updateOptions({
                    xaxis: {
                        labels: {
                            formatter: function (value) {
                                const strVal = String(value); // pastikan string
                                if (window.innerWidth < 768) {
                                    return strVal.split(" ")[0].substring(0, 3);
                                }
                                return strVal;
                            },
                        },
                    },
                });
            }
        }, 200);
    });

    function setLoadingRouters() {
        $("#routerOnline").html(
            '<i class="fas fa-spinner fa-spin text-muted"></i>'
        );
        $("#routerOffline").html(
            '<i class="fas fa-spinner fa-spin text-muted"></i>'
        );
    }

    function fetchDashboardCache() {
        const dashboardCacheUrl = window.dashboardCache;
        $.get(dashboardCacheUrl, function (data) {
            $("#routerOnline").text(data.countRouterOnline);
            $("#routerOffline").text(data.countRouterOffline);
            $("#customersOnline").text(data.sumCustomersOnline);
            $("#customersOffline").text(data.sumCustomersOffline);
            $("#customersIsolir").text(data.sumCustomersIsolir);
        });
    }

    // Trigger refresh sekali di awal
    setLoadingRouters();
    const dashboardTriggerUrl = window.dashboardTrigger;
    postWithCsrf(
        dashboardTriggerUrl,
        {},
        function (response) {
            fetchDashboardCache();
        },
        function (err) {
            console.error("[DASHBOARD TRIGGER ERROR]", err);
        }
    );
});

function postWithCsrf(url, data, onSuccess, onError) {
    $.ajax({
        url: url,
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: data || {},
        success: onSuccess,
        error: onError,
    });
}
