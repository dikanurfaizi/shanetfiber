<x-layouts.app-layout title="Dashboard">
    @section('custom_styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endsection
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

            <!-- Total Customer Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Total Customers</h6>
                                <h3 class="mb-0">
                                    <a href="{{ route('customers.index') }}" class="text-decoration-none">
                                        {{ $totalActiveCustomers ?? 0 }}
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
                                    <span class="text-success"> {{ $item->mikrotik_name }}</span>
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
                                <a href="{{ route('customers.index', ['status' => 'active']) }}"
                                    class="text-decoration-none">
                                    {{ $totalEnabled }} Active
                                </a>
                            </span>
                            <span class="text-warning mb-1">
                                <i class="fas fa-user-slash text-secondary"></i>
                                <a href="{{ route('customers.index', ['status' => 'inactive']) }}"
                                    class="text-decoration-none">
                                    {{ $totalDisabled }} Inactive
                                </a>
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-user-times text-danger"></i>
                                <a href="{{ route('customers.trashed') }}" class="text-decoration-none">
                                    {{ $totalNonActiveDisabled }} Suspend
                                </a>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                            <div class="col-md-6 col-lg-3 mt-2">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-body">
                                                        <h6 class="mb-1">{{ $adminData->admin_name }}</h6>
                                                        <h5 class="mb-3 text-primary text-end">@moneyIDR($adminData->total_billing)</h5>

                                                        <div class="border-top pt-2">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span>Total Customers:</span>
                                                                <strong class="text-end">
                                                                    <a href="{{ route('billings.index', [
                                                                        'admin_name' => $adminData->admin_name,
                                                                        'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                        'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    ]) }}"
                                                                        class="text-decoration-none">
                                                                        {{ number_format($adminData->total_customers, 0, ',', '.') }}
                                                                    </a>
                                                                </strong>
                                                            </div>
                                                        </div>

                                                        <div class="border-top pt-2">
                                                            <div class="row align-items-center g-0 mb-1">
                                                                <div class="col">
                                                                    <span>Paid:</span>
                                                                </div>
                                                                <div class="col-auto text-end">
                                                                    <div>
                                                                        <strong class="text-success">
                                                                            <a href="{{ route('billings.index', [
                                                                                'admin_name' => $adminData->admin_name,
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'LS',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-success">
                                                                                {{ number_format($adminData->total_paid_customers, 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData->total_paid, 0, ',', '.') }},00
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="border-top pt-2">
                                                            <div class="row align-items-center g-0 mb-1">
                                                                <div class="col">
                                                                    <span>Pay Later:</span>
                                                                </div>
                                                                <div class="col-auto text-end">
                                                                    <div>
                                                                        <strong class="text-warning">
                                                                            <a href="{{ route('billings.index', [
                                                                                'admin_name' => $adminData->admin_name,
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'PL',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-warning">
                                                                                {{ number_format($adminData->total_paylater_customers, 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData->total_paylater, 0, ',', '.') }},00
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="border-top pt-2">
                                                            <div class="row align-items-center g-0 mb-1">
                                                                <div class="col">
                                                                    <span>Unpaid:</span>
                                                                </div>
                                                                <div class="col-auto text-end">
                                                                    <div>
                                                                        <strong class="text-danger">
                                                                            <a href="{{ route('billings.index', [
                                                                                'admin_name' => $adminData->admin_name,
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'BL',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-danger">
                                                                                {{ number_format($adminData->total_unpaid_customers, 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData->total_unpaid, 0, ',', '.') }},00
                                                                    </div>
                                                                </div>
                                                            </div>
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

        <!-- Customer Registration/Churn Section -->
        @canany(['view-customer-registration-chart', 'view-customer-churn-chart'])
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Customer Movement Analysis</h5>
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-primary active" data-view-type="chart">
                                    <i class="fas fa-chart-line"></i> Chart View
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-view-type="table">
                                    <i class="fas fa-table"></i> Table View
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Chart View -->
                            <div id="customerMovementChartView">
                                <div id="customerRegistrationChurnChart" style="min-height: 350px;"></div>
                            </div>

                            <!-- Table View (Initially Hidden) -->
                            <div id="customerMovementTableView" style="display: none;">
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover" id="customerMovementTable">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Month</th>
                                                @if ($isSuperAdmin)
                                                    @foreach (array_keys($registrationData) as $adminId)
                                                        <th colspan="2" class="text-center">
                                                            {{ $registrationData[$adminId]['adminName'] }}</th>
                                                    @endforeach
                                                @else
                                                    <th>New Registrations</th>
                                                    <th>Churned Customers</th>
                                                @endif
                                            </tr>
                                            <tr>
                                                <th></th>
                                                @if ($isSuperAdmin)
                                                    @foreach (array_keys($registrationData) as $adminId)
                                                        <th class="text-end text-success">Registrations</th>
                                                        <th class="text-end text-danger">Churned</th>
                                                    @endforeach
                                                @else
                                                    <th class="text-end text-success">Registrations</th>
                                                    <th class="text-end text-danger">Churned</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($months as $index => $month)
                                                <tr>
                                                    <td>{{ $month }}</td>
                                                    @if ($isSuperAdmin)
                                                        @foreach (array_keys($registrationData) as $adminId)
                                                            <td class="text-end">
                                                                {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $churnData[$adminId]['counts'][$index] ?? 0 }}</td>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $adminId = array_keys($registrationData)[0];
                                                        @endphp
                                                        <td class="text-end">
                                                            {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}</td>
                                                        <td class="text-end">
                                                            {{ $churnData[$adminId]['counts'][$index] ?? 0 }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endcanany

        <!-- Popular Packages Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0 d-inline-block me-3">Popular Packages</h5>
                            <span class="badge text-white bg-primary">Total Packages:
                                {{ $packageSummary->count() }}</span>
                        </div>
                        <div class="btn-group btn-group-sm" role="group">
                            <button type="button" class="btn btn-outline-primary active" data-view="table">
                                <i class="fas fa-table"></i> Table View
                            </button>
                            <button type="button" class="btn btn-outline-primary" data-view="chart">
                                <i class="fas fa-chart-bar"></i> Chart View
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($packageSummary->isEmpty())
                            <div class="alert alert-warning m-3">
                                <i class="fas fa-info-circle me-2"></i> No package data available
                            </div>
                        @else
                            <!-- Table View -->
                            <div id="packageTableView" class="table-responsive"
                                style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light sticky-top" style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Package Name</th>
                                            <th class="text-end pe-4">Customers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalCustomers = 0;
                                            $maxCustomers = max(
                                                array_column($packageSummary->toArray(), 'total_customers'),
                                            );
                                        @endphp

                                        @foreach ($packageSummary as $index => $paket)
                                            @php
                                                $percentage =
                                                    $maxCustomers > 0
                                                        ? ($paket->total_customers / $maxCustomers) * 100
                                                        : 0;
                                                $totalCustomers += $paket->total_customers;
                                            @endphp
                                            <tr>
                                                <td class="ps-4">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 me-3" style="height: 20px;">
                                                            <div class="progress-bar bg-info" role="progressbar"
                                                                style="width: {{ $percentage }}%"
                                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span>{{ $paket->paket_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="fw-bold">{{ $paket->total_customers }}</span>
                                                    <small class="text-muted ms-1">customers</small>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr class="bg-light sticky-bottom">
                                            <td colspan="2" class="ps-4 fw-bold">Total Customers</td>
                                            <td class="text-end pe-4 fw-bold">{{ $totalCustomers }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Chart View (Hidden by Default) -->
                            <div id="packageChartView" style="display: none; height: 400px; padding: 15px;">
                                <div id="popularPackagesChart" style="height: 100%;"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

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
        <script>
            @if (app()->environment('local'))
                window.onlineRouters = [];
                // console.log("local",window.onlineRouters);
            @else
                window.onlineRouters = @json($onlineRouters);
                // console.log("production",window.onlineRouters);
            @endif

            window.packageData = @json($packageSummary);
            window.apifetchCustomerRegistrationUrl = "{{ route('customer.registration') }}";
            window.apiMonthlySummaryUrl = "{{ route('monthly.summary') }}";

            window.routes = {
                checkOnline: @json(route('check-online', ['id' => '__ID__'])),
                getPppSecrets: @json(route('get.ppp.secrets', ['mikrotik' => '__ROUTER__'])),
                getPppActive: @json(route('get.ppp.active', ['mikrotik' => '__ROUTER__'])),
                mikrotikResources: @json(route('mikrotik.resources', ['mikrotik' => '__ROUTER__']))
            };
        </script>
        <script src="{{ asset('assets/apexcharts/apexcharts.js') }}"></script>
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endsection
</x-layouts.app-layout>
