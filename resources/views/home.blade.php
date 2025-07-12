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
                                                        <h6 class="mb-1">{{ $adminData['admin_name'] }}</h6>
                                                        <h5 class="mb-3 text-primary text-end">@moneyIDR($adminData['total_billing'])</h5>

                                                        <div class="border-top pt-2">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span>Total Customers:</span>
                                                                <strong class="text-end">
                                                                    <a href="{{ route('billings.index', [
                                                                        'admin_name' => $adminData['admin_name'],
                                                                        'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                        'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                    ]) }}"
                                                                        class="text-decoration-none">
                                                                        {{ number_format($adminData['total_customers'], 0, ',', '.') }}
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
                                                                                'admin_name' => $adminData['admin_name'],
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'LS',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-success">
                                                                                {{ number_format($adminData['total_paid_customers'], 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData['total_paid'], 0, ',', '.') }},00
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
                                                                                'admin_name' => $adminData['admin_name'],
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'PL',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-warning">
                                                                                {{ number_format($adminData['total_paylater_customers'], 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData['total_paylater'], 0, ',', '.') }},00
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
                                                                                'admin_name' => $adminData['admin_name'],
                                                                                'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'),
                                                                                'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'),
                                                                                'search_status' => 'BL',
                                                                            ]) }}"
                                                                                class="text-decoration-none text-danger">
                                                                                {{ number_format($adminData['total_unpaid_customers'], 0, ',', '.') }}
                                                                            </a>
                                                                        </strong>
                                                                    </div>
                                                                    <div class="text-muted"
                                                                        style="font-size: 0.85rem;">
                                                                        Rp.
                                                                        {{ number_format($adminData['total_unpaid'], 0, ',', '.') }},00
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

    @section('custom_styles')
        <style>
            #customerRegistrationChurnChart {
                min-height: 400px;
                position: relative;
            }

            /* Untuk label miring */
            .apexcharts-xaxis-label {
                transform: translateY(5px);
                white-space: nowrap;
            }

            @media (max-width: 600px) {
                #customerRegistrationChurnChart {
                    min-height: 300px;
                }

                .apexcharts-xaxis-label {
                    font-size: 8px !important;
                }
            }
        </style>
        <style>
            /* Table styling */
            #customerMovementTable th {
                white-space: nowrap;
                position: sticky;
                top: 0;
                background-color: #f8f9fa;
            }

            #customerMovementTable .text-success {
                color: #28a745 !important;
            }

            #customerMovementTable .text-danger {
                color: #dc3545 !important;
            }

            /* Responsive adjustments */
            @media (max-width: 768px) {

                #customerMovementTable th,
                #customerMovementTable td {
                    padding: 0.5rem;
                    font-size: 0.85rem;
                }
            }
        </style>
        <style>
            /* Custom styling for the table */
            .table-responsive {
                scrollbar-width: thin;
                scrollbar-color: #dee2e6 #f8f9fa;
            }

            .table-responsive::-webkit-scrollbar {
                height: 8px;
                width: 8px;
            }

            .table-responsive::-webkit-scrollbar-thumb {
                background-color: #dee2e6;
                border-radius: 4px;
            }

            .table-responsive::-webkit-scrollbar-track {
                background-color: #f8f9fa;
            }

            /* Progress bar in table cells */
            .progress {
                background-color: #e9ecef;
                border-radius: 4px;
            }

            /* Sticky header and footer */
            .sticky-top {
                top: 0;
                position: sticky;
                z-index: 10;
            }

            .sticky-bottom {
                bottom: 0;
                position: sticky;
                z-index: 10;
            }

            /* Hover effects */
            .table-hover tbody tr:hover {
                background-color: rgba(0, 0, 0, 0.02);
            }
        </style>
    @endsection
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetchCustomerRegistrationChurnData();
                setInterval(fetchCustomerRegistrationChurnData, 300000);

                const viewButtons = document.querySelectorAll('[data-view-type]');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        viewButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');

                        if (this.dataset.viewType === 'table') {
                            document.getElementById('customerMovementChartView').style.display = 'none';
                            document.getElementById('customerMovementTableView').style.display =
                                'block';
                        } else {
                            document.getElementById('customerMovementChartView').style.display =
                                'block';
                            document.getElementById('customerMovementTableView').style.display = 'none';
                        }
                    });
                });

                // Initialize DataTable if needed
                if ($.fn.DataTable) {
                    $('#customerMovementTable').DataTable({
                        dom: '<"top"f>rt<"bottom"lip><"clear">',
                        pageLength: 12,
                        responsive: true,
                        order: [
                            [0, 'desc']
                        ],
                        language: {
                            search: "_INPUT_",
                            searchPlaceholder: "Search months...",
                        }
                    });
                }
            });

            async function fetchCustomerRegistrationChurnData() {
                try {
                    const apiUrl = "{{ route('customer.registration') }}";
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({})
                    });

                    if (!response.ok) {
                        const errorText = await response.text();
                        console.error('Network response not OK:', {
                            status: response.status,
                            statusText: response.statusText,
                            responseText: errorText
                        });
                        throw new Error(`Network response was not ok: ${response.status}`);
                    }

                    const data = await response.json();
                    if (data.error) {
                        console.error('API returned error:', data.error);
                        return;
                    }

                    renderCustomerRegistrationChurnChart(data);
                } catch (error) {
                    console.error('Error in fetchCustomerRegistrationChurnData:', {
                        errorName: error.name,
                        errorMessage: error.message,
                        errorStack: error.stack
                    });
                }
            }

            function renderCustomerRegistrationChurnChart(data) {
                try {
                    const {
                        months,
                        registrationData,
                        churnData,
                        isSuperAdmin
                    } = data;

                    if (!months || !registrationData) {
                        console.error('Invalid chart data:', {
                            months,
                            registrationData
                        });
                        return;
                    }

                    const series = [];
                    const colors = [];

                    if (isSuperAdmin) {
                        Object.keys(registrationData).forEach((adminId, index) => {
                            const adminName = registrationData[adminId]?.adminName || `Admin ${adminId}`;
                            const color = getColorForAdmin(index);

                            series.push({
                                name: `${adminName} (Signup)`,
                                data: registrationData[adminId]?.counts || [],
                                type: 'line'
                            });

                            series.push({
                                name: `${adminName} (Unsubscribe)`,
                                data: churnData[adminId]?.counts || Array(months.length).fill(0),
                                type: 'line'
                            });

                            colors.push(color, color);
                        });
                    } else {
                        const adminId = Object.keys(registrationData)[0];
                        if (adminId) {
                            series.push({
                                name: 'Signup',
                                data: registrationData[adminId]?.counts || [],
                                type: 'line'
                            });
                            series.push({
                                name: 'Unsubscribe',
                                data: churnData[adminId]?.counts || Array(months.length).fill(0),
                                type: 'line'
                            });
                            colors.push('#4e73df', '#e74a3b');
                        }
                    }

                    const options = {
                        series: series,
                        chart: {
                            type: 'line',
                            height: '100%',
                            toolbar: {
                                show: true
                            },
                            animations: {
                                enabled: false
                            },
                            zoom: {
                                enabled: true
                            }
                        },
                        colors: colors,
                        stroke: {
                            width: 3,
                            curve: 'smooth'
                        },
                        dataLabels: {
                            enabled: false
                        },
                        title: {
                            text: 'Monthly Customer Signup vs Unsubscribe Trend',
                            align: 'center',
                            style: {
                                fontSize: '16px'
                            }
                        },
                        xaxis: {
                            categories: months.map(m => m || ''),
                            labels: {
                                rotate: -45,
                                formatter: function(value) {
                                    try {
                                        if (!value) return '';
                                        const strValue = String(value).trim();
                                        if (window.innerWidth < 768) {
                                            const monthPart = strValue.split(' ')[0];
                                            return monthPart ? monthPart.substring(0, 3) : '';
                                        }
                                        return strValue;
                                    } catch (e) {
                                        console.warn('Error formatting label:', e);
                                        return '';
                                    }
                                }
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Number of Customers'
                            },
                            min: 0,
                            forceNiceScale: true,
                            labels: {
                                formatter: val => Math.floor(val)
                            }
                        },
                        legend: {
                            position: 'bottom',
                            horizontalAlign: 'center',
                            fontSize: '12px',
                            itemMargin: {
                                horizontal: 10
                            },
                            markers: {
                                radius: 5
                            }
                        },
                        grid: {
                            borderColor: '#f1f1f1',
                            padding: {
                                top: 20,
                                bottom: 40
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: val => `${val} customer`
                            }
                        },
                        responsive: [{
                            breakpoint: 600,
                            options: {
                                chart: {
                                    height: 300
                                },
                                xaxis: {
                                    labels: {
                                        rotate: -45,
                                        style: {
                                            fontSize: '8px'
                                        }
                                    }
                                },
                                legend: {
                                    fontSize: '10px',
                                    itemMargin: {
                                        horizontal: 5
                                    }
                                }
                            }
                        }]
                    };

                    const chartEl = document.getElementById("customerRegistrationChurnChart");
                    if (!chartEl) {
                        console.error('Chart container not found!');
                        return;
                    }

                    if (window.customerChart) {
                        window.customerChart.destroy();
                    }

                    chartEl.innerHTML = '';
                    window.customerChart = new ApexCharts(chartEl, options);
                    window.customerChart.render();
                } catch (error) {
                    console.error('Failed to render chart:', error);
                }
            }

            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    if (window.customerChart) {
                        window.customerChart.updateOptions({
                            xaxis: {
                                labels: {
                                    formatter: function(value) {
                                        if (window.innerWidth < 768) {
                                            return value.split(' ')[0].substring(0, 3);
                                        }
                                        return value;
                                    }
                                }
                            }
                        });
                    }
                }, 200);
            });

            function getColorForAdmin(index) {
                const colors = [
                    '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                    '#e74a3b', '#858796', '#5a5c69', '#3a3b45',
                    '#2e59d9', '#17a673', '#2c9faf', '#dda20a',
                    '#be2617', '#656773', '#42444e', '#24252e'
                ];
                return colors[index % colors.length];
            }
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // View Toggle Functionality
                const viewButtons = document.querySelectorAll('[data-view]');
                viewButtons.forEach(button => {
                    button.addEventListener('click', function() {
                        viewButtons.forEach(btn => btn.classList.remove('active'));
                        this.classList.add('active');

                        if (this.dataset.view === 'chart') {
                            document.getElementById('packageTableView').style.display = 'none';
                            document.getElementById('packageChartView').style.display = 'block';

                            // Render chart if not already rendered
                            if (!window.packageChart) {
                                renderPackageChart();
                            }
                        } else {
                            document.getElementById('packageTableView').style.display = 'block';
                            document.getElementById('packageChartView').style.display = 'none';
                        }
                    });
                });

                // Render Chart Function
                function renderPackageChart() {
                    const packageData = @json($packageSummary);
                    const categories = packageData.map(p => p.paket_name);
                    const seriesData = packageData.map(p => p.total_customers);

                    const options = {
                        series: [{
                            name: 'Customers',
                            data: seriesData
                        }],
                        chart: {
                            type: 'bar',
                            height: '100%',
                            toolbar: {
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: false,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: false,
                                    reset: true
                                }
                            }
                        },
                        colors: ['#4e73df'],
                        plotOptions: {
                            bar: {
                                borderRadius: 4,
                                horizontal: true,
                                distributed: false,
                                barHeight: '80%',
                            }
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val;
                            },
                            style: {
                                fontSize: '12px',
                                colors: ['#fff']
                            }
                        },
                        xaxis: {
                            categories: categories,
                            title: {
                                text: 'Number of Customers',
                                style: {
                                    fontSize: '12px'
                                }
                            },
                            labels: {
                                trim: true,
                                hideOverlappingLabels: true
                            }
                        },
                        yaxis: {
                            labels: {
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        tooltip: {
                            y: {
                                formatter: function(val) {
                                    return val + " customers";
                                }
                            }
                        },
                        responsive: [{
                            breakpoint: 600,
                            options: {
                                chart: {
                                    height: 400
                                },
                                plotOptions: {
                                    bar: {
                                        horizontal: false
                                    }
                                }
                            }
                        }]
                    };

                    window.packageChart = new ApexCharts(
                        document.querySelector("#popularPackagesChart"),
                        options
                    );
                    window.packageChart.render();
                }
            });
        </script>
    @endsection
</x-layouts.app-layout>
