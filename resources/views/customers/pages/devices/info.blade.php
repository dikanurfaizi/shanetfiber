<x-layouts.customers.app-layout title="Device Information">

    <div class="card-header py-3 bg-gradient-primary">
        <h5 class="m-0 fw-bold text-white d-flex align-items-center">
            <i class="fas fa-desktop me-3" style="font-size: 1.1em"></i>
            <span class="ms-2 text-wrap">{{ __('deviceInfo.title.device-info') }} -
                {{ auth()->guard('customer')->user()->name }}</span>
        </h5>
    </div>

    <div class="card-body">
        <div class="row">
            <!-- Device Information -->
            <div class="col-lg-6 d-flex">
                <div class="card shadow mb-4 w-100 h-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ __('deviceInfo.header.device-info') }}
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>{{ __('deviceInfo.label.status') }}</strong></td>
                                <td>
                                    :
                                    @if (!empty($secretActive['address']))
                                        <span class="badge bg-success text-white">
                                            {{ __('deviceInfo.label.online') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white">
                                            {{ __('deviceInfo.label.offline') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('deviceInfo.label.uptime') }}</strong></td>
                                <td>:
                                    {{ !empty($secretActive['uptime']) ? \App\Helpers\UptimeHelper::format($secretActive['uptime']) : '-' }}
                                </td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('deviceInfo.label.mac-address') }}</strong></td>
                                <td>: {{ !empty($secretActive['caller-id']) ? $secretActive['caller-id'] : '-' }}</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('deviceInfo.label.temperature') }}</strong></td>
                                <td>: N/A</td>
                            </tr>
                        </table>

                        <!-- tombol di pojok kanan bawah -->
                        <div class="mt-auto d-flex justify-content-end">
                            <button class="btn btn-sm btn-warning" onclick="showComingSoon()">
                                <i class="fas fa-sync-alt"></i> {{ __('deviceInfo.button.reboot') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- WLAN Information -->
            <div class="col-lg-6 d-flex">
                <div class="card shadow mb-4 w-100 h-100">
                    <div class="card-header py-3 bg-light">
                        <h6 class="m-0 fw-bold text-primary">
                            <i class="fas fa-wifi me-2"></i>
                            {{ __('deviceInfo.header.wlan-info') }}
                        </h6>
                    </div>
                    <div class="card-body d-flex flex-column">
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td width="40%"><strong>{{ __('deviceInfo.label.ssid-name') }}</strong></td>
                                <td>: N/A</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('deviceInfo.label.security') }}</strong></td>
                                <td>: N/A</td>
                            </tr>
                            <tr>
                                <td><strong>{{ __('deviceInfo.label.device-connection') }}</strong></td>
                                <td>: N/A</td>
                            </tr>
                        </table>

                        <!-- tombol di pojok kanan bawah -->
                        <div class="mt-auto d-flex justify-content-end">
                            <button class="btn btn-sm btn-primary" onclick="showComingSoon()">
                                <i class="fas fa-edit"></i> {{ __('deviceInfo.button.edit-ssid-pass') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Traffic Monitoring - Modern Minimalist with Moving Lines -->
        @if ($secretActive)
            <div class="card shadow mt-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('customer.header.traffic-monitoring') }}
                    </h6>
                </div>

                <div class="card-body col-lg-12 p-0">
                    <div class="px-4 pt-3">
                        <span class="badge bg-light text-dark border">
                            Interface: pppoe-{{ $customer->username }}
                        </span>
                    </div>

                    <div class="p-3">
                        <div id="dynamic-traffic-chart" style="min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        @else
            <div class="card shadow mt-4">
                <div class="card-header py-3 bg-light">
                    <h6 class="m-0 fw-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>
                        {{ __('deviceInfo.header.traffice-monitor') }}
                    </h6>
                </div>
                <div class="card-body">
                    <p>Traffic usage data will be displayed here.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Placeholder: Bandwidth graph integration (upload / download).
                    </div>
                </div>
            </div>
        @endif
    </div>

    @section('custom_styles')
        <style>
            .table-borderless td,
            .table-borderless th {
                border: none;
            }

            .card {
                border-radius: 0.5rem;
            }

            .card-header {
                border-radius: 0.5rem 0.5rem 0 0 !important;
            }

            .badge {
                font-size: 0.85em;
                padding: 0.4em 0.6em;
            }

            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, 0.03);
            }
        </style>
    @endsection

    @section('custom_scripts')
        <script src="{{ @asset('vendor/larapex-charts/apexcharts.js') }}"></script>
        <script>
            function showComingSoon() {
                Swal.fire({
                    icon: 'info',
                    title: 'Coming Soon',
                    text: 'Fitur ini sedang dikembangkan.',
                    confirmButtonText: 'OK'
                });
            }

            $(document).ready(function() {
                // Initialize with sample data to make lines visible immediately
                let initialDataPoints = 15;
                let timeData = Array.from({
                    length: initialDataPoints
                }, (_, i) => i);
                let downloadData = Array(initialDataPoints).fill(0);
                let uploadData = Array(initialDataPoints).fill(0);

                const options = {
                    chart: {
                        height: '100%',
                        type: "line",
                        animations: {
                            enabled: true,
                            easing: 'linear',
                            speed: 1000,
                            animateGradually: {
                                enabled: true,
                                delay: 150
                            },
                            dynamicAnimation: {
                                enabled: true,
                                speed: 1000
                            }
                        },
                        toolbar: {
                            show: false
                        },
                        zoom: {
                            enabled: false
                        },
                        events: {
                            mounted: function(ctx) {
                                ctx.windowResizeHandler();
                                // Force initial animation
                                setTimeout(() => {
                                    ctx.updateSeries([{
                                        data: downloadData
                                    }, {
                                        data: uploadData
                                    }], true);
                                }, 300);
                            }
                        }
                    },
                    series: [{
                        name: 'Download',
                        data: downloadData
                    }, {
                        name: 'Upload',
                        data: uploadData
                    }],
                    stroke: {
                        curve: "smooth",
                        width: 2,
                        dashArray: [0, 0],
                        lineCap: 'round'
                    },
                    colors: ["#0090fc", "#ff6666"],
                    fill: {
                        type: "gradient",
                        gradient: {
                            shadeIntensity: 0.5,
                            opacityFrom: 0.7,
                            opacityTo: 0.3,
                            stops: [0, 90, 100]
                        }
                    },
                    markers: {
                        size: 0,
                        hover: {
                            size: 4
                        }
                    },
                    xaxis: {
                        categories: timeData,
                        labels: {
                            show: false
                        },
                        axisBorder: {
                            show: false
                        },
                        axisTicks: {
                            show: false
                        },
                        tooltip: {
                            enabled: false
                        },
                        range: initialDataPoints
                    },
                    yaxis: {
                        labels: {
                            formatter: formatTraffic
                        },
                        min: 0,
                        forceNiceScale: true
                    },
                    grid: {
                        borderColor: '#f1f1f1',
                        strokeDashArray: 3,
                        padding: {
                            top: 0,
                            right: 10,
                            bottom: 0,
                            left: 10
                        }
                    },
                    tooltip: {
                        enabled: true,
                        y: {
                            formatter: formatTraffic
                        },
                        x: {
                            show: false
                        }
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    },
                    dataLabels: {
                        enabled: false
                    },
                    noData: {
                        text: 'Loading data...',
                        align: 'center',
                        verticalAlign: 'middle'
                    }
                };

                @if ($secretActive && $customer->username)
                    const chart = new ApexCharts(document.querySelector("#dynamic-traffic-chart"), options);
                    chart.render().then(() => {
                        // Initial data load
                        updateChart();
                        // Start regular updates
                        setInterval(updateChart, 2000);
                    });

                    function formatTraffic(value) {
                        let numericValue = Number(value);

                        if (isNaN(numericValue) || numericValue <= 0) {
                            return '0 bps';
                        }

                        const sizes = ['bps', 'kbps', 'Mbps', 'Gbps', 'Tbps'];
                        const i = Math.floor(Math.log(numericValue) / Math.log(1024));

                        // Mencegah index out of bounds jika angka sangat kecil/besar
                        const index = Math.min(Math.max(i, 0), sizes.length - 1);

                        return parseFloat((numericValue / Math.pow(1024, index)).toFixed(2)) + ' ' + sizes[index];
                    }

                    function updateChart() {
                        const iface = "<pppoe-{{ $customer->username }}>";
                        const url = "{{ route('customer.device.traffic_interface') }}";

                        $.ajax({
                            url: url,
                            data: {
                                'interface': iface
                            },
                            success: function(response) {
                                // Tangani struktur response baik jika berbentuk array langsung atau terbungkus di response.original
                                let rawData = response;
                                if (response && response.original) {
                                    rawData = response.original;
                                }

                                if (rawData && rawData[0]) {
                                    // 1. Process download data (Tx)
                                    let newDownloadData = [...downloadData];
                                    if (rawData[0].Tx && rawData[0].Tx.length > 0) {
                                        let lastTxObj = rawData[0].Tx[rawData[0].Tx.length - 1];
                                        // 💡 AMBIL NILAI .y DAN CONVERT KE INT (Default 0 jika null/NaN)
                                        let txValue = parseInt(lastTxObj.y || lastTxObj) || 0;

                                        newDownloadData.shift();
                                        newDownloadData.push(txValue);
                                    }

                                    // 2. Process upload data (Rx)
                                    let newUploadData = [...uploadData];
                                    if (rawData[0].Rx && rawData[0].Rx.length > 0) {
                                        let lastRxObj = rawData[0].Rx[rawData[0].Rx.length - 1];
                                        // 💡 AMBIL NILAI .y DAN CONVERT KE INT (Default 0 jika null/NaN)
                                        let rxValue = parseInt(lastRxObj.y || lastRxObj) || 0;

                                        newUploadData.shift();
                                        newUploadData.push(rxValue);
                                    }

                                    // 3. Update chart series secara atomic
                                    chart.updateSeries([{
                                        name: 'Download',
                                        data: newDownloadData
                                    }, {
                                        name: 'Upload',
                                        data: newUploadData
                                    }], true);

                                    // Update reference arrays
                                    downloadData = newDownloadData;
                                    uploadData = newUploadData;
                                }
                            },
                            error: function(xhr) {
                                console.error("Error fetching traffic data:", xhr.statusText);
                            },
                            dataType: 'json'
                        });
                    }
                @endif
            });
        </script>
    @endsection
</x-layouts.customers.app-layout>
