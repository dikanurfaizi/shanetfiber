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
        <div class="row mb-4">
            <!-- Servers Card -->
            <div class="col-12 col-md-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase text-muted mb-2">Total Servers</h6>
                                <h3 class="mb-0">
                                    <a href="{{ route('servers.index') }}" class="text-decoration-none">
                                        {{ $totalRouters }}
                                    </a>
                                </h3>
                            </div>
                            <div class="bg-primary-soft text-primary d-flex align-items-center justify-content-center rounded"
                                style="width: 48px; height: 48px;">
                                <i class="fas fa-server"></i>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between">
                            <span class="text-success">
                                <i class="fas fa-check-circle mr-1"></i>
                                <a href="{{ route('servers.index', ['disabled' => 'false']) }}"
                                    class="text-decoration-none">
                                    {{ $routerEnabled }} Enabled
                                </a>
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-times-circle mr-1"></i>
                                <a href="{{ route('servers.index', ['disabled' => 'true']) }}"
                                    class="text-decoration-none">
                                    {{ $routerDisabled }} Disabled
                                </a>
                            </span>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-success">
                                <i class="fas fa-wifi mr-1"></i>
                                <span id="routerOnline"><i class="fas fa-spinner fa-spin text-muted"></i></span> Online
                            </span>
                            <span class="text-danger">
                                <i class="fas fa-plug mr-1"></i>
                                <span id="routerOffline"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                Offline
                            </span>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Total Customer Card -->
            <div class="col-12 col-md-4 mb-4">
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
                            <div class="bg-success-soft text-success d-flex align-items-center justify-content-center rounded"
                                style="width: 48px; height: 48px;">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <hr class="my-3">
                        <ul class="list-unstyled mb-0">
                            @foreach ($perMikrotik ?? [] as $item)
                                <li class="d-flex justify-content-between">
                                    <span class="text-success"> {{ $item->mikrotik_name }}</span>
                                    <span class="font-weight-bold">{{ $item->total_customers }} Customers</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customers Status Card -->
            <div class="col-12 col-md-4 mb-4">
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
                            <div class="bg-success-soft text-success d-flex align-items-center justify-content-center rounded"
                                style="width: 48px; height: 48px;">
                                <i class="fas fa-users-cog"></i>
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
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0">Billing Period: {{ $lastBillingPeriode->name ?? 'No Billing Period' }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Total Billings -->
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="border-left border-3 border-primary p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Total Billings</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index') }}" class="text-decoration-none">
                                                    {{ $lastBillings->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-primary-soft text-primary">customers</span>
                                        </div>
                                        @if ($totalBilling !== null)
                                            <h5 class="mt-2 text-primary">@moneyIDR($totalBilling)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Paid -->
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="border-left border-3 border-success p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Paid</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=LS', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'LS')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-success-soft text-success">customers</span>
                                        </div>
                                        @if ($totalPaid !== null)
                                            <h5 class="mt-2 text-success">@moneyIDR($totalPaid)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pay Later -->
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="border-left border-3 border-warning p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Pay Later</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=PL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'PL')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-warning-soft text-warning">customers</span>
                                        </div>
                                        @if ($totalPaylater !== null)
                                            <h5 class="mt-2 text-warning">@moneyIDR($totalPaylater)</h5>
                                        @endif
                                    </div>
                                </div>

                                <!-- Unpaid -->
                                <div class="col-md-6 col-lg-3 mb-4">
                                    <div class="border-left border-3 border-danger p-3 h-100">
                                        <h6 class="text-uppercase text-muted mb-2">Unpaid</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="mb-0">
                                                <a href="{{ route('billings.index', ['search_status=BL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                    class="text-decoration-none">
                                                    {{ $lastBillings->where('status', 'BL')->count() }}
                                                </a>
                                            </h3>
                                            <span class="badge bg-danger-soft text-danger">customers</span>
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
                                    <div class="row">
                                        @foreach ($adminBillingSummary as $adminData)
                                            <div class="col-md-6 col-lg-3 mb-4">
                                                <div class="card border-0 shadow-sm h-100">
                                                    <div class="card-body">
                                                        <h6 class="mb-1">{{ $adminData->admin_name }}</h6>
                                                        <h5 class="mb-3 text-primary text-right">@moneyIDR($adminData->total_billing)</h5>

                                                        <div class="border-top pt-2">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span>Total Customers:</span>
                                                                <strong class="text-right">
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
                                                                <div class="col-auto text-right">
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
                                                                <div class="col-auto text-right">
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
                                                                <div class="col-auto text-right">
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
            'view-billings-chart-customer-count', 'investor-billing-chart'])
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
        @canany(['view-customer-registration-chart', 'view-customer-churn-chart', 'investor-billing-chart'])
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
                                                        <th class="text-right text-success">Registrations</th>
                                                        <th class="text-right text-danger">Churned</th>
                                                    @endforeach
                                                @else
                                                    <th class="text-right text-success">Registrations</th>
                                                    <th class="text-right text-danger">Churned</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($months as $index => $month)
                                                <tr>
                                                    <td>{{ $month }}</td>
                                                    @if ($isSuperAdmin)
                                                        @foreach (array_keys($registrationData) as $adminId)
                                                            <td class="text-right">
                                                                {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}
                                                            </td>
                                                            <td class="text-right">
                                                                {{ $churnData[$adminId]['counts'][$index] ?? 0 }}</td>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $adminId = array_keys($registrationData)[0];
                                                        @endphp
                                                        <td class="text-right">
                                                            {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}</td>
                                                        <td class="text-right">
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
                            <h5 class="mb-0 d-inline-block mr-3">Popular Packages</h5>
                            <span class="badge text-primary bg-primary-soft">Total Packages:
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
                                <i class="fas fa-info-circle mr-2"></i> No package data available
                            </div>
                        @else
                            <!-- Table View -->
                            <div id="packageTableView" class="table-responsive"
                                style="max-height: 500px; overflow-y: auto;">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light" style="background-color: #f8f9fa;">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Package Name</th>
                                            <th class="text-right pe-4">Customers</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalCustomers = 0;
                                            $maxCustomers = max(
                                                array_column($packageSummary->toArray(), 'total_customers'),
                                            );
                                        @endphp

                                        @foreach ($packageSummary as $index => $package)
                                            @php
                                                $percentage =
                                                    $maxCustomers > 0
                                                        ? ($package->total_customers / $maxCustomers) * 100
                                                        : 0;
                                                $totalCustomers += $package->total_customers;
                                            @endphp
                                            <tr>
                                                <td class="ps-4">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress flex-grow-1 mr-3" style="height: 20px;">
                                                            <div class="progress-bar bg-info" role="progressbar"
                                                                style="width: {{ $percentage }}%"
                                                                aria-valuenow="{{ $percentage }}" aria-valuemin="0"
                                                                aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <span>{{ $package->package_name }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-right pe-4">
                                                    <a href="{{ route('customers.index', ['search_package' => $package->package_id]) }}"
                                                        class="text-decoration-none">
                                                        <span class="fw-bold">{{ $package->total_customers }}</span>
                                                        <small class="text-muted ml-1">customers</small>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr class="bg-light sticky-bottom">
                                            <td colspan="2" class="ps-4 fw-bold">Total Customers</td>
                                            <td class="text-right pe-4 fw-bold">{{ $totalCustomers }}</td>
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
        <div class="row">
            @foreach ($mikrotiks as $mikrotik)
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex flex-column justify-content-between"
                            style="min-height: 111px;">
                            <div class="d-flex flex-column">
                                <h5 class="mb-2">
                                    <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}"
                                        class="text-decoration-none">
                                        <i id="status-{{ $mikrotik->slug }}"
                                            class="fas fa-power-off text-danger mr-2"></i>
                                        {{ $mikrotik->name }}
                                    </a>
                                </h5>
                                <div>
                                    <span class="badge bg-light text-dark mr-1 router-badge-text"
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
                                        <i class="fas fa-microchip mr-1"></i> CPU Usage
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
                                        <i class="fas fa-memory mr-1"></i> RAM Usage
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
                                        <i class="fas fa-hard-drive mr-1"></i> Disk Usage
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
                window.mikrotiks = [];
                // console.log("local", window.mikrotiks);
            @else
                window.mikrotiks = @json($mikrotiks);
                // console.log("production", window.mikrotiks);
            @endif

            window.packageData = @json($packageSummary);
            window.apifetchCustomerRegistrationUrl = "{{ route('customer.registration') }}";
            window.apiMonthlySummaryUrl = "{{ route('monthly.summary') }}";
            window.dashboardTrigger = "{{ route('dashboard.trigger') }}";
            window.dashboardCache = "{{ route('dashboard.cache') }}";

            window.routes = {
                // checkOnline: @json(route('check-online', ['id' => '__ID__'])),
                // getPppSecrets: @json(route('get.ppp.secrets', ['mikrotik' => '__ROUTER__'])),
                // getPppActive: @json(route('get.ppp.active', ['mikrotik' => '__ROUTER__'])),
                updateCache: @json(route('router.update.cache', ['mikrotik' => '__ROUTER__'])),
                getResource: @json(route('mikrotik.resources', ['mikrotik' => '__ROUTER__']))
            };
        </script>
        <script src="{{ asset('assets/apexcharts/apexcharts.js') }}"></script>
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endsection
</x-layouts.app-layout>
