<x-layouts.app-layout title="Dashboard">
    @section('custom_styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endsection

    <div class="container-fluid px-4 py-4 bg-light">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-1">Dashboard Overview</h1>
                <p class="text-muted small mb-0">Pantauan status server, pelanggan, dan billing bulanan Anda.</p>
            </div>
        </div>

        <!-- Stats Cards Row -->
        <div class="row mb-4">
            <!-- Servers Card -->
            <div class="col-12 col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm card-modern h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold small tracking-wider">Total
                                    Servers</span>
                                <h2 class="mb-0 font-weight-bold mt-1">
                                    <a href="{{ route('servers.index') }}" class="text-dark text-decoration-none">
                                        {{ $totalRouters }}
                                    </a>
                                </h2>
                            </div>
                            <div class="icon-shape bg-primary-soft">
                                <i class="fas fa-server h5 mb-0"></i>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex flex-column">
                            <div class="d-flex justify-content-between small mb-2">
                                <span>
                                    <span class="badge bg-success-soft rounded-pill mr-1"><i
                                            class="fas fa-check-circle"></i></span>
                                    <a href="{{ route('servers.index', ['disabled' => 'false']) }}"
                                        class="text-muted text-decoration-none font-weight-bold">
                                        {{ $routerEnabled }} Enabled
                                    </a>
                                </span>
                                <span>
                                    <span class="badge bg-danger-soft rounded-pill mr-1"><i
                                            class="fas fa-times-circle"></i></span>
                                    <a href="{{ route('servers.index', ['disabled' => 'true']) }}"
                                        class="text-muted text-decoration-none font-weight-bold">
                                        {{ $routerDisabled }} Disabled
                                    </a>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted font-weight-bold">
                                    <i class="fas fa-wifi text-success mr-1"></i>
                                    <span id="routerOnline"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    Online
                                </span>
                                <span class="text-muted font-weight-bold">
                                    <i class="fas fa-plug text-danger mr-1"></i>
                                    <span id="routerOffline"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    Offline
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Customer Card -->
            <div class="col-12 col-md-4 mb-3 mb-md-0">
                <div class="card border-0 shadow-sm card-modern h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold small tracking-wider">Total
                                    Customers</span>
                                <h2 class="mb-0 font-weight-bold mt-1">
                                    <a href="{{ route('customers.index') }}" class="text-dark text-decoration-none">
                                        {{ $totalActiveCustomers ?? 0 }}
                                    </a>
                                </h2>
                            </div>
                            <div class="icon-shape bg-success-soft">
                                <i class="fas fa-users h5 mb-0"></i>
                            </div>
                        </div>

                        <div class="pt-3 border-top">
                            <ul class="list-unstyled mb-0 d-flex flex-column small">
                                @forelse ($perMikrotik ?? [] as $item)
                                    <li class="d-flex justify-content-between align-items-center py-1">
                                        <span class="text-secondary font-weight-bold">{{ $item->mikrotik_name }}</span>
                                        <span
                                            class="badge bg-light text-dark font-weight-bold border">{{ $item->total_customers }}
                                            Customers</span>
                                    </li>
                                @empty
                                    <li class="text-muted small">Tidak ada data router</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customers Status Card -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm card-modern h-100 rounded-3">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span class="text-uppercase text-muted font-weight-bold small tracking-wider">Customers
                                    Status</span>
                                <h2 class="mb-0 font-weight-bold mt-1">
                                    <a href="{{ route('customers.index') }}" class="text-dark text-decoration-none">
                                        {{ $totalCustomers ?? 0 }}
                                    </a>
                                </h2>
                            </div>
                            <div class="icon-shape bg-info-soft">
                                <i class="fas fa-users-cog h5 mb-0"></i>
                            </div>
                        </div>

                        <div class="pt-3 border-top d-flex justify-content-between text-center">
                            <!-- Active -->
                            <div class="flex-fill p-2 rounded bg-success-soft mx-1">
                                <a href="{{ route('customers.index', ['status' => 'active']) }}"
                                    class="text-decoration-none">
                                    <div class="font-weight-bold h4 mb-0 text-success">{{ $totalEnabled }}</div>
                                    <small class="text-success font-weight-bold">Active</small>
                                </a>
                            </div>

                            <!-- Inactive -->
                            <div class="flex-fill p-2 rounded bg-warning-soft mx-1">
                                <a href="{{ route('customers.index', ['status' => 'inactive']) }}"
                                    class="text-decoration-none">
                                    <div class="font-weight-bold h4 mb-0 text-warning">{{ $totalDisabled }}</div>
                                    <small class="text-warning font-weight-bold">Inactive</small>
                                </a>
                            </div>

                            <!-- Suspend -->
                            <div class="flex-fill p-2 rounded bg-danger-soft mx-1">
                                <a href="{{ route('customers.trashed') }}" class="text-decoration-none">
                                    <div class="font-weight-bold h4 mb-0 text-danger">{{ $totalNonActiveDisabled }}
                                    </div>
                                    <small class="text-danger font-weight-bold">Suspend</small>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Billing Section -->
        @if ($lastBillings)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div
                            class="card-header card-header-clean py-3 d-flex align-items-center justify-content-between">
                            <h5 class="mb-0 font-weight-bold text-dark">
                                <i class="far fa-calendar-alt mr-2 text-primary"></i> Billing Period: <span
                                    class="text-primary">{{ $lastBillingPeriode->name ?? 'No Billing Period' }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Total Billings -->
                                <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-primary-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span
                                                class="text-uppercase font-weight-bold small text-primary opacity-75">Total
                                                Billings</span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="font-weight-bold mb-0 text-primary">
                                                    <a href="{{ route('billings.index') }}"
                                                        class="text-decoration-none text-primary">
                                                        {{ $lastBillings->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="font-weight-bold text-primary opacity-75">customers</small>
                                            </div>
                                        </div>
                                        @if ($totalBilling !== null)
                                            <div
                                                class="mt-3 font-weight-bold text-primary border-top border-primary border-opacity-10 pt-2">
                                                @moneyIDR($totalBilling)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Paid -->
                                <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-success-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span
                                                class="text-uppercase font-weight-bold small text-success opacity-75">Paid</span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="font-weight-bold mb-0 text-success">
                                                    <a href="{{ route('billings.index', ['search_status=LS', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-success">
                                                        {{ $lastBillings->where('status', 'LS')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="font-weight-bold text-success opacity-75">customers</small>
                                            </div>
                                        </div>
                                        @if ($totalPaid !== null)
                                            <div
                                                class="mt-3 font-weight-bold text-success border-top border-success border-opacity-10 pt-2">
                                                @moneyIDR($totalPaid)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pay Later -->
                                <div class="col-md-6 col-lg-3 mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-warning-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span
                                                class="text-uppercase font-weight-bold small text-warning opacity-75">Pay
                                                Later</span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="font-weight-bold mb-0 text-warning">
                                                    <a href="{{ route('billings.index', ['search_status=PL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-warning">
                                                        {{ $lastBillings->where('status', 'PL')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="font-weight-bold text-warning opacity-75">customers</small>
                                            </div>
                                        </div>
                                        @if ($totalPaylater !== null)
                                            <div
                                                class="mt-3 font-weight-bold text-warning border-top border-warning border-opacity-10 pt-2">
                                                @moneyIDR($totalPaylater)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Unpaid -->
                                <div class="col-md-6 col-lg-3">
                                    <div
                                        class="p-3 rounded-3 bg-danger-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span
                                                class="text-uppercase font-weight-bold small text-danger opacity-75">Unpaid</span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="font-weight-bold mb-0 text-danger">
                                                    <a href="{{ route('billings.index', ['search_status=BL', 'search_month=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year=' . Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-danger">
                                                        {{ $lastBillings->where('status', 'BL')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="font-weight-bold text-danger opacity-75">customers</small>
                                            </div>
                                        </div>
                                        @if ($totalUnpaid !== null)
                                            <div
                                                class="mt-3 font-weight-bold text-danger border-top border-danger border-opacity-10 pt-2">
                                                @moneyIDR($totalUnpaid)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Admin Breakdown -->
                            @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                <div class="mt-4 pt-3 border-top">
                                    <h6 class="text-muted font-weight-bold mb-3 small text-uppercase tracking-wider">
                                        Details Per Admin</h6>
                                    <div class="row">
                                        @foreach ($adminBillingSummary as $adminData)
                                            <div class="col-md-6 col-lg-3 mb-3">
                                                <div class="card border shadow-none rounded-3 h-100">
                                                    <div class="card-body p-3">
                                                        <!-- Header Card: Nama Admin & Total Nominal -->
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <h6 class="mb-0 font-weight-bold text-dark">
                                                                {{ $adminData->admin_name }}</h6>
                                                            <span
                                                                class="badge bg-light text-primary border font-weight-bold">
                                                                @moneyIDR($adminData->total_billing)
                                                            </span>
                                                        </div>

                                                        <div class="d-flex flex-column mt-3 small">
                                                            <!-- Baris Customers dengan Garis Bawah -->
                                                            <div
                                                                class="d-flex justify-content-between text-muted pb-2 mb-2 border-bottom">
                                                                <span>Customers:</span>
                                                                <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                                    class="text-dark font-weight-bold text-decoration-none">
                                                                    {{ number_format($adminData->total_customers, 0, ',', '.') }}
                                                                </a>
                                                            </div>

                                                            <!-- Baris Paid -->
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span
                                                                    class="text-success font-weight-bold">Paid:</span>
                                                                <div class="text-right">
                                                                    <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'LS']) }}"
                                                                        class="text-success font-weight-bold text-decoration-none">
                                                                        {{ number_format($adminData->total_paid_customers, 0, ',', '.') }}
                                                                    </a>
                                                                    <div class="text-muted small">Rp
                                                                        {{ number_format($adminData->total_paid, 0, ',', '.') }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Baris Pay Later -->
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <span class="text-warning font-weight-bold">Pay
                                                                    Later:</span>
                                                                <div class="text-right">
                                                                    <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'PL']) }}"
                                                                        class="text-warning font-weight-bold text-decoration-none">
                                                                        {{ number_format($adminData->total_paylater_customers, 0, ',', '.') }}
                                                                    </a>
                                                                    <div class="text-muted small">Rp
                                                                        {{ number_format($adminData->total_paylater, 0, ',', '.') }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Baris Unpaid -->
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <span
                                                                    class="text-danger font-weight-bold">Unpaid:</span>
                                                                <div class="text-right">
                                                                    <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'BL']) }}"
                                                                        class="text-danger font-weight-bold text-decoration-none">
                                                                        {{ number_format($adminData->total_unpaid_customers, 0, ',', '.') }}
                                                                    </a>
                                                                    <div class="text-muted small">Rp
                                                                        {{ number_format($adminData->total_unpaid, 0, ',', '.') }}
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

        <!-- Charts Section (Side by Side Grid) -->
        <div class="row mb-4">
            <!-- Chart Kiri: Monthly Customer & Billing Summary -->
            @canany(['view-billings-chart-nominal-total', 'view-billings-chart-nominal-related',
                'view-billings-chart-customer-count', 'investor-billing-chart'])
                <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div id="chartWrapper1" style="min-height: 380px;"></div>
                        </div>
                    </div>
                </div>
            @endcanany

            <!-- Chart Kanan: Customer Registration Analysis -->
            @canany(['view-customer-registration-chart', 'view-customer-churn-chart', 'investor-billing-chart'])
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header card-header-clean py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 font-weight-bold text-dark">Customer Registration Analysis</h5>
                            <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                                <button type="button"
                                    class="btn btn-sm btn-white text-primary rounded-pill shadow-xs active"
                                    data-view-type="chart">
                                    <i class="fas fa-chart-line mr-1"></i> Chart
                                </button>
                                <button type="button" class="btn btn-sm text-muted rounded-pill" data-view-type="table">
                                    <i class="fas fa-table mr-1"></i> Table
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <!-- Chart View -->
                            <div id="customerMovementChartView">
                                <div id="customerRegistrationChurnChart" style="min-height: 350px;"></div>
                            </div>

                            <!-- Table View (Initially Hidden) -->
                            <div id="customerMovementTableView" style="display: none;">
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="customerMovementTable">
                                        <thead class="bg-light small text-muted sticky-top">
                                            <tr>
                                                <th>Month</th>
                                                @if ($isSuperAdmin)
                                                    @foreach (array_keys($registrationData) as $adminId)
                                                        <th class="text-center">
                                                            {{ $registrationData[$adminId]['adminName'] }}</th>
                                                    @endforeach
                                                @else
                                                    <th class="text-right text-success">New Registrations</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach ($months as $index => $month)
                                                <tr>
                                                    <td class="font-weight-bold">{{ $month }}</td>
                                                    @if ($isSuperAdmin)
                                                        @foreach (array_keys($registrationData) as $adminId)
                                                            <td class="text-center font-weight-bold text-success">
                                                                {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}
                                                            </td>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $adminId = array_keys($registrationData)[0] ?? null;
                                                        @endphp
                                                        <td class="text-right font-weight-bold text-success">
                                                            {{ $adminId ? $registrationData[$adminId]['counts'][$index] ?? 0 : 0 }}
                                                        </td>
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
            @endcanany
        </div>

        <!-- Popular Packages Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header card-header-clean py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 font-weight-bold text-dark mr-2">Popular Packages</h5>
                            <span class="badge bg-primary-soft rounded-pill">Total:
                                {{ $packageSummary->count() }}</span>
                        </div>
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                            <button type="button"
                                class="btn btn-sm btn-white text-primary rounded-pill shadow-xs active"
                                data-view="table">
                                <i class="fas fa-table mr-1"></i> Table
                            </button>
                            <button type="button" class="btn btn-sm text-muted rounded-pill" data-view="chart">
                                <i class="fas fa-chart-bar mr-1"></i> Chart
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($packageSummary->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-info-circle mr-1"></i> No package data available
                            </div>
                        @else
                            <div id="packageTableView" class="table-responsive" style="max-height: 450px;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light small text-muted sticky-top">
                                        <tr>
                                            <th class="pl-4" style="width: 50px;">#</th>
                                            <th>Package Name</th>
                                            <th class="text-right pr-4">Customers</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
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
                                                <td class="pl-4 text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="font-weight-bold mr-3"
                                                            style="min-width: 140px;">{{ $package->package_name }}</span>
                                                        <div class="progress progress-soft flex-grow-1"
                                                            style="height: 6px;">
                                                            <div class="progress-bar bg-primary" role="progressbar"
                                                                style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-right pr-4">
                                                    <a href="{{ route('customers.index', ['search_package' => $package->package_id]) }}"
                                                        class="text-decoration-none">
                                                        <span
                                                            class="font-weight-bold text-dark">{{ $package->total_customers }}</span>
                                                        <small class="text-muted ml-1">users</small>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr class="bg-light sticky-bottom font-weight-bold">
                                            <td colspan="2" class="pl-4">Total Customers</td>
                                            <td class="text-right pr-4">{{ $totalCustomers }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div id="packageChartView" data-summary='@json($packageSummary)'
                                style="display: none; height: 380px; padding: 20px;">
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
                <div class="col-sm-6 col-lg-4 col-xl-3 mb-3">
                    <div class="card border-0 shadow-sm card-modern h-100 rounded-3">
                        <div class="card-header card-header-clean py-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <h6 class="mb-0 font-weight-bold">
                                    <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}"
                                        class="text-dark text-decoration-none">
                                        <i id="status-{{ $mikrotik->slug }}"
                                            class="fas fa-power-off text-danger mr-2"></i>
                                        {{ $mikrotik->name }}
                                    </a>
                                </h6>
                            </div>
                            <div class="d-flex">
                                <span class="badge bg-light text-secondary border font-weight-normal mr-1"
                                    id="boardname-{{ $mikrotik->slug }}">--</span>
                                <span class="badge bg-light text-secondary border font-weight-normal"
                                    id="uptime-{{ $mikrotik->slug }}">--</span>
                            </div>
                        </div>
                        <div class="card-body p-3 small">
                            <!-- CPU -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-microchip mr-1"></i> CPU Usage</span>
                                    <small id="cpu-{{ $mikrotik->slug }}"
                                        class="font-weight-bold text-dark">0%</small>
                                </div>
                                <div class="progress progress-soft" style="height: 6px;">
                                    <div id="cpu-bar-{{ $mikrotik->slug }}" class="progress-bar bg-primary"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- RAM -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-memory mr-1"></i> RAM Usage</span>
                                    <small id="memory-{{ $mikrotik->slug }}"
                                        class="font-weight-bold text-dark">Loading...</small>
                                </div>
                                <div class="progress progress-soft" style="height: 6px;">
                                    <div id="ram-bar-{{ $mikrotik->slug }}" class="progress-bar bg-warning"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- HDD -->
                            <div>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-hard-drive mr-1"></i> Disk Usage</span>
                                    <small id="hdd-{{ $mikrotik->slug }}"
                                        class="font-weight-bold text-dark">Loading...</small>
                                </div>
                                <div class="progress progress-soft" style="height: 6px;">
                                    <div id="hdd-bar-{{ $mikrotik->slug }}" class="progress-bar bg-info"
                                        role="progressbar" style="width: 0%"></div>
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
            @else
                window.mikrotiks = @json($mikrotiks);
            @endif

            window.packageData = @json($packageSummary);
            window.apifetchCustomerRegistrationUrl = "{{ route('customer.registration') }}";
            window.apiMonthlySummaryUrl = "{{ route('monthly.summary') }}";
            window.dashboardTrigger = "{{ route('dashboard.trigger') }}";
            window.dashboardCache = "{{ route('dashboard.cache') }}";

            window.routes = {
                updateCache: @json(route('router.update.cache', ['mikrotik' => '__ROUTER__'])),
                getResource: @json(route('mikrotik.resources', ['mikrotik' => '__ROUTER__']))
            };
        </script>
        <script src="{{ asset('assets/apexcharts/apexcharts.js') }}"></script>
        <script src="{{ asset('js/dashboard.js') }}"></script>
    @endsection
</x-layouts.app-layout>
