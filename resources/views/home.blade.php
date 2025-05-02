<x-layouts.app-layout title="Dashboard">
    <div class="container-fluid px-4 py-3">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 text-gray-800">Dashboard Overview</h1>
        </div>

        <!-- Stats Cards Row -->
        {{-- <div class="row g-4 mb-4"> --}}
        <div class="row g-3 mb-4">
            <!-- Servers Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Total Servers</h6>
                                <h3 class="mb-0">
                                    <a href="{{ route('servers.index') }}" class="text-decoration-none">
                                        {{ $mikrotiks->count() ?? 0 }}
                                    </a>
                                </h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-3 rounded">
                                <i class="fas fa-server text-white fs-4"></i>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between">
                            <span class="text-success">
                                <i class="fas fa-check-circle me-1"></i>
                                <a href="{{ route('servers.index', ['disabled' => 'false']) }}"
                                    class="text-decoration-none">
                                    {{ $routersEnable->count() }} Active
                                </a>
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-times-circle me-1"></i>
                                <a href="{{ route('servers.index', ['disabled' => 'true']) }}"
                                    class="text-decoration-none">
                                    {{ $mikrotiks->count() - $routersEnable->count() }} Inactive
                                </a>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-success">
                                <i class="fas fa-wifi me-1"></i> {{ $countRouterOnline }} Online
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-plug me-1"></i> {{ $countRouterOffline }} Offline
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Server Status Card -->
            {{-- <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Server Status</h6>
                                <h3 class="mb-0">{{ $countRouterOnline + $countRouterOffline }}</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 p-3 rounded">
                                <i class="fas fa-network-wired text-white fs-4"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-success">
                                <i class="fas fa-wifi me-1"></i> {{ $countRouterOnline }} Online
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-plug me-1"></i> {{ $countRouterOffline }} Offline
                            </span>
                        </div>
                    </div>
                </div>
            </div> --}}

            <!-- Total Customer Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Total Customers</h6>
                                <h3 class="mb-0">
                                    <a href="{{ route('customers.index') }}" class="text-decoration-none">
                                        {{ $totalCustomers ?? 0 }}
                                    </a>
                                </h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users text-white fs-4"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <ul class="list-unstyled mb-0">
                            @foreach ($perMikrotik ?? [] as $item)
                                <li class="d-flex justify-content-between">
                                    <span class="text-dark">{{ $item->mikrotik_name }}</span>
                                    <span class="fw-semibold">{{ $item->total_customers }} Customers</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customers Status Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Customers Status</h6>
                                <h3 class="mb-0">
                                    <a href="{{ route('customers.index') }}" class="text-decoration-none">
                                        {{ $totalCustomers ?? 0 }}
                                    </a>
                                </h3>
                            </div>
                            <div class="bg-success bg-opacity-10 p-3 rounded">
                                <i class="fas fa-users-cog text-white fs-4"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex flex-column mb-2">
                            <span class="text-success mb-1">
                                <i class="fas fa-user-check"></i>
                                <a href="{{ route('customers.index', ['status' => 'false']) }}"
                                    class="text-decoration-none">
                                    {{ $totalEnabled }} Active
                                </a>
                            </span>
                            <span class="text-warning mb-1">
                                <i class="fas fa-user-slash text-secondary"></i>
                                <a href="{{ route('customers.index', ['status' => 'true']) }}"
                                    class="text-decoration-none">
                                    {{ $totalDisabled }} Disabled
                                </a>
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-user-times text-danger"></i>
                                <a href="{{ route('customers.index', ['status' => 'deactivated']) }}"
                                    class="text-decoration-none">
                                    {{ $totalNonActiveDisabled }} Deactivated
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Status Card -->
        {{-- <div class="col-md-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Connection Status</h6>
                                <h3 class="mb-0">{{ $totalCustomers ?? 0 }}</h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-3 rounded">
                                <i class="fas fa-signal text-white fs-4"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex flex-column">
                            <span class="text-success mb-1">
                                <i class="fas fa-link me-1"></i>
                                <a href="{{ route('customers.index', ['status' => 'online']) }}"
                                    class="text-decoration-none">
                                    {{ $sumCustomersOnline - $sumCustomersIsolir }} Connected
                                </a>
                            </span>
                            <span class="text-danger mb-1">
                                <i class="fas fa-unlink me-1"></i>
                                <a href="{{ route('customers.index', ['status' => 'offline']) }}"
                                    class="text-decoration-none">
                                    {{ $sumCustomersOffline }} Disconnected
                                </a>
                            </span>
                            <span class="text-secondary">
                                <i class="fas fa-ban me-1"></i>
                                <a href="{{ route('customers.index', ['status' => 'isolir']) }}"
                                    class="text-decoration-none">
                                    {{ $sumCustomersIsolir - $totalNonActiveDisabled }} Isolir
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
    </div> --}}

        <!-- Billing Section -->
        @if ($lastBillings)
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0">Billing Period: {{ $lastBillingPeriode->name ?? 'No Billing Period' }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <!-- Total Billings -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="border-start border-3 border-primary p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Total Billings</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index') }}" class="text-decoration-none">
                                                    {{ $lastBillings->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-primary bg-opacity-10 text-white">customers</span>
                                        </div>
                                        @if ($totalBilling !== null)
                                            <h5 class="mt-2 text-primary">@moneyIDR($totalBilling)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Paid -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="border-start border-3 border-success p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Paid</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=LS', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'LS')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-success bg-opacity-10 text-white">customers</span>
                                        </div>
                                        @if ($totalPaid !== null)
                                            <h5 class="mt-2 text-success">@moneyIDR($totalPaid)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pay Later -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="border-start border-3 border-warning p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Pay Later</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=PL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'PL')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-warning bg-opacity-10 text-white">customers</span>
                                        </div>
                                        @if ($totalPaylater !== null)
                                            <h5 class="mt-2 text-warning">@moneyIDR($totalPaylater)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Unpaid -->
                                <div class="col-md-6 col-lg-3">
                                    <div class="border-start border-3 border-danger p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Unpaid</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=BL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'BL')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-danger bg-opacity-10 text-white">customers</span>
                                        </div>
                                        @if ($totalUnpaid !== null)
                                            <h5 class="mt-2 text-danger">@moneyIDR($totalUnpaid)</h5>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Breakdown -->
                            @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                <div class="mt-4">
                                    <h6 class="text-muted mb-3">Details Per Admin</h6>
                                    <div class="row g-3">
                                        @foreach ($adminBillingSummary as $adminData)
                                            <div class="col-md-6 col-lg-3">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-body">
                                                        <h6 class="mb-3">{{ $adminData['admin_name'] }}</h6>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Total Customers:</span>
                                                            <strong>
                                                                <a href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                ]) }}"
                                                                    class="text-decoration-none">
                                                                    {{ $adminData['total_customers'] }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Paid:</span>
                                                            <strong class="text-success">
                                                                <a href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=LS',
                                                                ]) }}"
                                                                    class="text-decoration-none text-success">
                                                                    {{ $adminData['total_paid_customers'] }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span>Pay Later:</span>
                                                            <strong class="text-warning">
                                                                <a href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=PL',
                                                                ]) }}"
                                                                    class="text-decoration-none text-warning">
                                                                    {{ $adminData['total_paylater_customers'] }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span>Unpaid:</span>
                                                            <strong class="text-danger">
                                                                <a href="{{ route('billings.index', [
                                                                    'admin_name' => $adminData['admin_name'],
                                                                    'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                    'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    'search_status=BL',
                                                                ]) }}"
                                                                    class="text-decoration-none text-danger">
                                                                    {{ $adminData['total_unpaid_customers'] }}
                                                                </a>
                                                            </strong>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Charts Section -->
        @canany(['view-billings-chart-nominal-total', 'view-billings-chart-nominal-related',
            'view-billings-chart-customer-count'])
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div id="chartWrapper1"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endcanany

        <!-- Router Status Section -->
        <div class="row g-4">
            @foreach ($routersEnable as $mikrotik)
                <div class="col-md-6 col-xl-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <div class="d-flex flex-column">
                                <h5 class="mb-2">
                                    <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}"
                                        class="text-decoration-none">
                                        <i id="status-{{ $mikrotik->slug }}"
                                            class="fas fa-power-off text-danger me-2"></i>
                                        {{ $mikrotik->name }}
                                    </a>
                                </h5>
                                <div style="min-height: 50px;">
                                    <span class="badge bg-light text-dark me-1"
                                        id="boardname-{{ $mikrotik->slug }}"></span>
                                    <span class="badge bg-light text-dark" id="uptime-{{ $mikrotik->slug }}"></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- CPU -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">
                                        <i class="fas fa-microchip me-1"></i> CPU Usage
                                    </span>
                                    <small id="cpu-{{ $mikrotik->slug }}">0%</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="cpu-bar-{{ $mikrotik->slug }}"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%; background-color: #4e73df"></div>
                                </div>
                            </div>

                            <!-- RAM -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">
                                        <i class="fas fa-memory me-1"></i> RAM Usage
                                    </span>
                                    <small id="memory-{{ $mikrotik->slug }}">Loading...</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="ram-bar-{{ $mikrotik->slug }}"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%; background-color: #f6c23e"></div>
                                </div>
                            </div>

                            <!-- HDD -->
                            <div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">
                                        <i class="fas fa-hard-drive me-1"></i> Disk Usage
                                    </span>
                                    <small id="hdd-{{ $mikrotik->slug }}">Loading...</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div id="hdd-bar-{{ $mikrotik->slug }}"
                                        class="progress-bar progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: 0%; background-color: #36b9cc"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            let chart;

            async function fetchMonthlySummary() {
                try {
                    const response = await fetch('/api/fetch-monthly-summary', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    });

                    const data = await response.json() || {};
                    const {
                        billingSummary = [], permissions = []
                    } = data ?? {};

                    if (!permissions.length) {
                        document.querySelector("#chartWrapper1").innerHTML =
                            "<div class='text-center p-4 text-muted'>No data available</div>";
                        return;
                    }

                    const months = billingSummary.map(item => item.month_name || '');
                    const totalPaid = billingSummary.map(item => item.total_paid || 0);
                    const totalUnpaid = billingSummary.map(item => item.total_unpaid || 0);
                    const totalPaylater = billingSummary.map(item => item.total_paylater || 0);
                    const totalCustomers = billingSummary.map(item => item.total_customers || 0);

                    let series = [];

                    if (permissions.includes('view-billings-chart-nominal-total') ||
                        permissions.includes('view-billings-chart-nominal-related')) {
                        series = [{
                                name: 'Unpaid (Rp)',
                                data: totalUnpaid,
                                color: '#dc3545'
                            },
                            {
                                name: 'Paid (Rp)',
                                data: totalPaid,
                                color: '#28a745'
                            }
                        ];
                    } else if (permissions.includes('view-billings-chart-customer-count')) {
                        series = [{
                                name: 'Unpaid',
                                data: totalUnpaid,
                                color: '#dc3545'
                            },
                            {
                                name: 'Paylater',
                                data: totalPaylater,
                                color: '#ffc107'
                            },
                            {
                                name: 'Paid',
                                data: totalPaid,
                                color: '#28a745'
                            }
                        ];
                    }

                    const yAxisMax = Math.max(...totalPaid, ...totalUnpaid, ...totalCustomers) * 1.25;

                    const options = {
                        chart: {
                            type: 'bar',
                            height: 350,
                            stacked: true,
                            toolbar: {
                                show: false
                            }
                        },
                        title: {
                            text: 'Monthly Customer Summary',
                            align: 'center',
                            style: {
                                fontSize: '18px',
                                fontWeight: 'bold',
                                color: '#333'
                            }
                        },
                        series,
                        xaxis: {
                            categories: months,
                            labels: {
                                rotate: -45
                            }
                        },
                        yaxis: {
                            min: 0,
                            max: yAxisMax,
                            labels: {
                                formatter: value =>
                                    permissions.includes('view-billings-chart-nominal-total') ||
                                    permissions.includes('view-billings-chart-nominal-related') ?
                                    'Rp ' + value.toLocaleString() : `${Math.round(value)} customers`
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: value =>
                                    permissions.includes('view-billings-chart-nominal-total') ||
                                    permissions.includes('view-billings-chart-nominal-related') ?
                                    'Rp ' + value.toLocaleString() : `${Math.round(value)} customers`
                            }
                        },
                        dataLabels: {
                            enabled: false
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'center'
                        }
                    };

                    if (chart) chart.destroy();
                    chart = new ApexCharts(document.querySelector("#chartWrapper1"), options);
                    chart.render();

                } catch (error) {
                    console.error('Error fetching data: ', error);
                }
            }

            fetchMonthlySummary();
            setInterval(fetchMonthlySummary, 300000);
        </script>

        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).on('click', '#check-online', function(e) {
                e.preventDefault();
                const selectedRouterId = $(this).data('id');
                const url = "{{ route('check-online', ':id') }}".replace(':id', selectedRouterId);

                $.ajax({
                    url: url,
                    method: "POST",
                    cache: false,
                    data: {
                        'id': selectedRouterId
                    },
                    success: function(response) {
                        new Notify({
                            status: response.status,
                            title: response.title,
                            text: response.message,
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    },
                    error: function(data) {
                        new Notify({
                            status: 'error',
                            title: 'Connection failed!',
                            text: data.responseJSON?.message || 'An error occurred',
                            effect: 'slide',
                            speed: 500,
                            showCloseButton: true,
                            autotimeout: 5000,
                            autoclose: true
                        });
                    }
                });
            });

            @if ($onlineRouters)
                @foreach ($onlineRouters as $onlineRouter)
                    $(document).ready(function() {
                        $("#loading_ppp_secret-{{ $onlineRouter->slug }}").removeClass("d-none");
                        $("#loading_ppp_non_active-{{ $onlineRouter->slug }}").removeClass("d-none");
                        $("#loading_ppp_active-{{ $onlineRouter->slug }}").removeClass("d-none");

                        var allPppSecretsCount = '';
                        var allPppActiveCount = '';

                        function updateCountPppSecret() {
                            $.ajax({
                                url: "{{ route('get.ppp.secrets', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    allPppSecretsCount = response.allPppSecretsCount;
                                    $("#ppp-secrets-count-{{ $onlineRouter->slug }}").text(
                                        allPppSecretsCount);
                                },
                                error: console.error
                            });
                        }

                        function updateCountPppActive() {
                            $.ajax({
                                url: "{{ route('get.ppp.active', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    allPppActiveCount = response.allPppActiveCount;
                                    $("#ppp-secrets-active-count-{{ $onlineRouter->slug }}").text(
                                        allPppActiveCount);
                                },
                                error: console.error
                            });
                        }

                        function updateCountPppNonActive() {
                            const total = allPppSecretsCount - allPppActiveCount;
                            $("#ppp-secrets-non-active-count-{{ $onlineRouter->slug }}").text(total);
                        }

                        function updateSystemResources() {
                            $.ajax({
                                url: "{{ route('mikrotik.resources', ['mikrotik' => $onlineRouter]) }}",
                                method: "GET",
                                success: function(response) {
                                    if (response.resources?.length > 0) {
                                        const resource = response.resources[0];
                                        const memoryUse = resource.totalMemory - resource.freeMemory;
                                        const memoryPercentUse = Math.round(memoryUse / resource
                                            .totalMemory * 100);
                                        const hddUse = resource.totalHdd - resource.freeHdd;
                                        const hddPercentUse = Math.round(hddUse / resource.totalHdd * 100);

                                        $('#cpu-bar-{{ $onlineRouter->slug }}').css('width', resource
                                            .cpuLoad + '%');
                                        $('#cpu-{{ $onlineRouter->slug }}').text(resource.cpuLoad + '%');

                                        $('#ram-bar-{{ $onlineRouter->slug }}').css('width',
                                            memoryPercentUse + '%');
                                        $('#memory-{{ $onlineRouter->slug }}').text(resource.memoryUsage +
                                            '/' + resource.totalMemorySpace);

                                        $('#hdd-bar-{{ $onlineRouter->slug }}').css('width',
                                            hddPercentUse + '%');
                                        $('#hdd-{{ $onlineRouter->slug }}').text(resource.hddUsage + '/' +
                                            resource.totalHddSpace);

                                        $('#boardname-{{ $onlineRouter->slug }}').text(resource.boardName);
                                        $('#uptime-{{ $onlineRouter->slug }}').text(resource.uptime);
                                        $("#status-{{ $onlineRouter->slug }}").removeClass("text-danger")
                                            .addClass("text-success");
                                    }
                                },
                                error: console.error
                            });
                        }

                        async function updateData() {
                            await updateCountPppActive();
                            await updateCountPppSecret();
                            await updateCountPppNonActive();
                            await updateSystemResources();
                        }

                        updateData()
                            .catch(console.error);

                        setInterval(updateSystemResources, 10000);
                        setInterval(updateCountPppNonActive, 5000);
                        setInterval(function() {
                            updateCountPppSecret();
                            updateCountPppActive();
                        }, 10000);

                        $(document).ajaxStop(function() {
                            $("#loading_ppp_secret-{{ $onlineRouter->slug }}").addClass("d-none");
                            $("#loading_ppp_non_active-{{ $onlineRouter->slug }}").addClass("d-none");
                            $("#loading_ppp_active-{{ $onlineRouter->slug }}").addClass("d-none");
                        });
                    });
                @endforeach
            @endif
        </script>
    @endsection
</x-layouts.app-layout>
