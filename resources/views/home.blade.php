<x-layouts.app-layout :title="__('dashboard.title')">

    @push('custom_styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    <div class="container-fluid px-4 py-4 bg-light">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold text-dark mb-1">{{ __('dashboard.title') }}</h1>
                <p class="text-muted small mb-0">{{ __('dashboard.subtitle') }}</p>
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
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">
                                    {{ __('dashboard.cards.total_servers') }}
                                </span>
                                <h2 class="mb-0 fw-bold mt-1">
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
                                    <span class="badge bg-success-soft rounded-pill me-1">
                                        <i class="fas fa-check-circle"></i>
                                    </span>
                                    <a href="{{ route('servers.index', ['disabled' => 'false']) }}"
                                        class="text-muted text-decoration-none fw-bold">
                                        {{ $routerEnabled }} {{ __('dashboard.cards.enabled') }}
                                    </a>
                                </span>
                                <span>
                                    <span class="badge bg-danger-soft rounded-pill me-1">
                                        <i class="fas fa-times-circle"></i>
                                    </span>
                                    <a href="{{ route('servers.index', ['disabled' => 'true']) }}"
                                        class="text-muted text-decoration-none fw-bold">
                                        {{ $routerDisabled }} {{ __('dashboard.cards.disabled') }}
                                    </a>
                                </span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span class="text-muted fw-bold">
                                    <i class="fas fa-wifi text-success me-1"></i>
                                    <span id="routerOnline"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    {{ __('dashboard.cards.online') }}
                                </span>
                                <span class="text-muted fw-bold">
                                    <i class="fas fa-plug text-danger me-1"></i>
                                    <span id="routerOffline"><i class="fas fa-spinner fa-spin text-muted"></i></span>
                                    {{ __('dashboard.cards.offline') }}
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
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">
                                    {{ __('dashboard.cards.total_customers') }}
                                </span>
                                <h2 class="mb-0 fw-bold mt-1">
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
                                        <span class="text-secondary fw-bold">{{ $item->mikrotik_name }}</span>
                                        <span class="badge bg-light text-dark fw-bold border">
                                            {{ $item->total_customers }} {{ __('dashboard.cards.customers_unit') }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="text-muted small">{{ __('dashboard.cards.no_router_data') }}</li>
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
                                <span class="text-uppercase text-muted fw-bold small tracking-wider">
                                    {{ __('dashboard.cards.customers_status') }}
                                </span>
                                <h2 class="mb-0 fw-bold mt-1">
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
                                    <div class="fw-bold h4 mb-0 text-success">{{ $totalEnabled }}</div>
                                    <small class="text-success fw-bold">{{ __('dashboard.cards.active') }}</small>
                                </a>
                            </div>

                            <!-- Inactive -->
                            <div class="flex-fill p-2 rounded bg-warning-soft mx-1">
                                <a href="{{ route('customers.index', ['status' => 'inactive']) }}"
                                    class="text-decoration-none">
                                    <div class="fw-bold h4 mb-0 text-warning">{{ $totalDisabled }}</div>
                                    <small class="text-warning fw-bold">{{ __('dashboard.cards.inactive') }}</small>
                                </a>
                            </div>

                            <!-- Suspend -->
                            <div class="flex-fill p-2 rounded bg-danger-soft mx-1">
                                <a href="{{ route('customers.trashed') }}" class="text-decoration-none">
                                    <div class="fw-bold h4 mb-0 text-danger">{{ $totalNonActiveDisabled }}
                                    </div>
                                    <small class="text-danger fw-bold">{{ __('dashboard.cards.suspend') }}</small>
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
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="far fa-calendar-alt me-2 text-primary"></i>
                                {{ __('dashboard.cards.billing_period') }}
                                <span
                                    class="text-primary">{{ $lastBillingPeriode->name ?? __('dashboard.cards.no_billing_period') }}</span>
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Total Billings -->
                                <div class="col-12 col-sm-6 col-lg mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-primary-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="text-uppercase fw-bold small text-primary opacity-75">
                                                {{ __('dashboard.cards.total_billings') }}
                                            </span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="fw-bold mb-0 text-primary">
                                                    <a href="{{ route('billings.index') }}"
                                                        class="text-decoration-none text-primary">
                                                        {{ $lastBillings->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="fw-bold text-primary opacity-75">{{ __('dashboard.cards.customers_unit') }}</small>
                                            </div>
                                        </div>
                                        @if ($totalBilling !== null)
                                            <div
                                                class="mt-3 fw-bold text-primary border-top border-primary border-opacity-10 pt-2">
                                                @moneyIDR($totalBilling)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Paid -->
                                <div class="col-12 col-sm-6 col-lg mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-success-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="text-uppercase fw-bold small text-success opacity-75">
                                                {{ __('dashboard.cards.paid') }}
                                            </span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="fw-bold mb-0 text-success">
                                                    <a href="{{ route('billings.index', ['search_status' => 'LS', 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-success">
                                                        {{ $lastBillings->where('status', 'LS')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="fw-bold text-success opacity-75">{{ __('dashboard.cards.customers_unit') }}</small>
                                            </div>
                                        </div>
                                        @if ($totalPaid !== null)
                                            <div
                                                class="mt-3 fw-bold text-success border-top border-success border-opacity-10 pt-2">
                                                @moneyIDR($totalPaid)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Pay Later -->
                                <div class="col-12 col-sm-6 col-lg mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-warning-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="text-uppercase fw-bold small text-warning opacity-75">
                                                {{ __('dashboard.cards.pay_later') }}
                                            </span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="fw-bold mb-0 text-warning">
                                                    <a href="{{ route('billings.index', ['search_status' => 'PL', 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-warning">
                                                        {{ $lastBillings->where('status', 'PL')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="fw-bold text-warning opacity-75">{{ __('dashboard.cards.customers_unit') }}</small>
                                            </div>
                                        </div>
                                        @if ($totalPaylater !== null)
                                            <div
                                                class="mt-3 fw-bold text-warning border-top border-warning border-opacity-10 pt-2">
                                                @moneyIDR($totalPaylater)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Unpaid -->
                                <div class="col-12 col-sm-6 col-lg mb-3 mb-lg-0">
                                    <div
                                        class="p-3 rounded-3 bg-danger-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="text-uppercase fw-bold small text-danger opacity-75">
                                                {{ __('dashboard.cards.unpaid') }}
                                            </span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="fw-bold mb-0 text-danger">
                                                    <a href="{{ route('billings.index', ['search_status' => 'BL', 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-danger">
                                                        {{ $lastBillings->where('status', 'BL')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="fw-bold text-danger opacity-75">{{ __('dashboard.cards.customers_unit') }}</small>
                                            </div>
                                        </div>
                                        @if ($totalUnpaid !== null)
                                            <div
                                                class="mt-3 fw-bold text-danger border-top border-danger border-opacity-10 pt-2">
                                                @moneyIDR($totalUnpaid)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Void / Hangus -->
                                <div class="col-12 col-sm-6 col-lg">
                                    <div
                                        class="p-3 rounded-3 bg-secondary-soft h-100 d-flex flex-column justify-content-between">
                                        <div>
                                            <span class="text-uppercase fw-bold small text-secondary opacity-75">
                                                {{ __('dashboard.cards.void') }}
                                            </span>
                                            <div class="d-flex justify-content-between align-items-baseline mt-2">
                                                <h3 class="fw-bold mb-0 text-secondary">
                                                    <a href="{{ route('billings.index', ['search_status' => 'VD', 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                        class="text-decoration-none text-secondary">
                                                        {{ $lastBillings->where('status', 'VD')->count() }}
                                                    </a>
                                                </h3>
                                                <small
                                                    class="fw-bold text-secondary opacity-75">{{ __('dashboard.cards.customers_unit') }}</small>
                                            </div>
                                        </div>
                                        @if ($totalVoid !== null)
                                            <div
                                                class="mt-3 fw-bold text-secondary border-top border-secondary border-opacity-10 pt-2">
                                                @moneyIDR($totalVoid)
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <!-- Admin Breakdown -->
                                @if ($adminBillingSummary && $adminBillingSummary->count() > 0)
                                    <div class="mt-4 pt-3 border-top">
                                        <h6 class="text-muted fw-bold mb-3 small text-uppercase tracking-wider">
                                            {{ __('dashboard.cards.details_per_admin') }}
                                        </h6>
                                        <div class="row">
                                            @foreach ($adminBillingSummary as $adminData)
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <div class="card border shadow-none rounded-3 h-100">
                                                        <div class="card-body p-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                <h6 class="mb-0 fw-bold text-dark">
                                                                    {{ $adminData->admin_name }}
                                                                </h6>
                                                                <span
                                                                    class="badge bg-light text-primary border fw-bold">
                                                                    @moneyIDR($adminData->total_billing)
                                                                </span>
                                                            </div>

                                                            <div class="d-flex flex-column mt-3 small">
                                                                <div
                                                                    class="d-flex justify-content-between text-muted pb-2 mb-2 border-bottom">
                                                                    <span>{{ __('dashboard.cards.customers_label') }}</span>
                                                                    <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y')]) }}"
                                                                        class="text-dark fw-bold text-decoration-none">
                                                                        {{ number_format($adminData->total_customers, 0, ',', '.') }}
                                                                    </a>
                                                                </div>

                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span
                                                                        class="text-success fw-bold">{{ __('dashboard.cards.paid') }}:</span>
                                                                    <div class="text-end">
                                                                        <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'LS']) }}"
                                                                            class="text-success fw-bold text-decoration-none">
                                                                            {{ number_format($adminData->total_paid_customers, 0, ',', '.') }}
                                                                        </a>
                                                                        <div class="text-muted small">Rp
                                                                            {{ number_format($adminData->total_paid, 0, ',', '.') }}
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span
                                                                        class="text-warning fw-bold">{{ __('dashboard.cards.pay_later') }}:</span>
                                                                    <div class="text-end">
                                                                        <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'PL']) }}"
                                                                            class="text-warning fw-bold text-decoration-none">
                                                                            {{ number_format($adminData->total_paylater_customers, 0, ',', '.') }}
                                                                        </a>
                                                                        <div class="text-muted small">Rp
                                                                            {{ number_format($adminData->total_paylater, 0, ',', '.') }}
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="d-flex justify-content-between align-items-center mb-2">
                                                                    <span
                                                                        class="text-danger fw-bold">{{ __('dashboard.cards.unpaid') }}:</span>
                                                                    <div class="text-end">
                                                                        <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'BL']) }}"
                                                                            class="text-danger fw-bold text-decoration-none">
                                                                            {{ number_format($adminData->total_unpaid_customers, 0, ',', '.') }}
                                                                        </a>
                                                                        <div class="text-muted small">Rp
                                                                            {{ number_format($adminData->total_unpaid, 0, ',', '.') }}
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <span
                                                                        class="text-secondary fw-bold">{{ __('dashboard.cards.void') }}:</span>
                                                                    <div class="text-end">
                                                                        <a href="{{ route('billings.index', ['admin_name' => $adminData->admin_name, 'search_month' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('F'), 'search_year' => Carbon\Carbon::parse($lastBillingPeriode->name)->format('Y'), 'search_status' => 'VD']) }}"
                                                                            class="text-secondary fw-bold text-decoration-none">
                                                                            {{ number_format($adminData->total_void_customers, 0, ',', '.') }}
                                                                        </a>
                                                                        <div class="text-muted small">Rp
                                                                            {{ number_format($adminData->total_void, 0, ',', '.') }}
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
            </div>
        @endif

        <!-- Charts Section -->
        <div class="row mb-4">
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

            @canany(['view-customer-registration-chart', 'view-customer-churn-chart', 'investor-billing-chart'])
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-3 h-100">
                        <div class="card-header card-header-clean py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold text-dark">{{ __('dashboard.charts.registration_analysis') }}
                            </h5>
                            <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                                <button type="button"
                                    class="btn btn-sm btn-white text-primary rounded-pill shadow-xs active"
                                    data-view-type="chart">
                                    <i class="fas fa-chart-line me-1"></i> {{ __('dashboard.charts.view_chart') }}
                                </button>
                                <button type="button" class="btn btn-sm text-muted rounded-pill" data-view-type="table">
                                    <i class="fas fa-table me-1"></i> {{ __('dashboard.charts.view_table') }}
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div id="customerMovementChartView">
                                <div id="customerRegistrationChurnChart" style="min-height: 350px;"></div>
                            </div>

                            <div id="customerMovementTableView" style="display: none;">
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-hover align-middle mb-0" id="customerMovementTable">
                                        <thead class="bg-light small text-muted sticky-top">
                                            <tr>
                                                <th>{{ __('dashboard.charts.month') }}</th>
                                                @if ($isSuperAdmin)
                                                    @foreach (array_keys($registrationData) as $adminId)
                                                        <th class="text-center">
                                                            {{ $registrationData[$adminId]['adminName'] }}</th>
                                                    @endforeach
                                                @else
                                                    <th class="text-end text-success">
                                                        {{ __('dashboard.charts.new_registrations') }}</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="small">
                                            @foreach ($months as $index => $month)
                                                <tr>
                                                    <td class="fw-bold">{{ $month }}</td>
                                                    @if ($isSuperAdmin)
                                                        @foreach (array_keys($registrationData) as $adminId)
                                                            <td class="text-center fw-bold text-success">
                                                                {{ $registrationData[$adminId]['counts'][$index] ?? 0 }}
                                                            </td>
                                                        @endforeach
                                                    @else
                                                        @php
                                                            $adminId = array_keys($registrationData)[0] ?? null;
                                                        @endphp
                                                        <td class="text-end fw-bold text-success">
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

        <!-- Popular Packages & Unpaid Customers Section -->
        <div class="row mb-4">
            <!-- Popular Packages -->
            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header card-header-clean py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 fw-bold text-dark me-2">{{ __('dashboard.packages.title') }}</h5>
                            <span class="badge bg-primary-soft rounded-pill">{{ __('dashboard.packages.total') }}
                                {{ $packageSummary->count() }}</span>
                        </div>
                        <div class="btn-group btn-group-sm p-1 bg-light rounded-pill" role="group">
                            <button type="button"
                                class="btn btn-sm btn-white text-primary rounded-pill shadow-xs active"
                                data-view="table">
                                <i class="fas fa-table me-1"></i> {{ __('dashboard.charts.view_table') }}
                            </button>
                            <button type="button" class="btn btn-sm text-muted rounded-pill" data-view="chart">
                                <i class="fas fa-chart-bar me-1"></i> {{ __('dashboard.charts.view_chart') }}
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if ($packageSummary->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-info-circle me-1"></i> {{ __('dashboard.packages.no_data') }}
                            </div>
                        @else
                            <div id="packageTableView" class="table-responsive" style="max-height: 400px;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light small text-muted sticky-top">
                                        <tr>
                                            <th class="ps-4" style="width: 50px;">#</th>
                                            <th>{{ __('dashboard.packages.package_name') }}</th>
                                            <th class="text-end pe-4">{{ __('dashboard.cards.total_customers') }}
                                            </th>
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
                                                <td class="ps-4 text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <span class="fw-bold me-3"
                                                            style="min-width: 120px;">{{ $package->package_name }}</span>
                                                        <div class="progress progress-soft flex-grow-1"
                                                            style="height: 6px;">
                                                            <div class="progress-bar bg-primary" role="progressbar"
                                                                style="width: {{ $percentage }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <a href="{{ route('customers.index', ['search_package' => $package->package_id]) }}"
                                                        class="text-decoration-none">
                                                        <span
                                                            class="fw-bold text-dark">{{ $package->total_customers }}</span>
                                                        <small
                                                            class="text-muted ms-1">{{ __('dashboard.cards.users_unit') }}</small>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr class="bg-light sticky-bottom fw-bold">
                                            <td colspan="2" class="ps-4">
                                                {{ __('dashboard.packages.total_customers') }}</td>
                                            <td class="text-end pe-4">{{ $totalCustomers }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div id="packageChartView" data-summary='@json($packageSummary)'
                                style="display: none; height: 350px; padding: 20px;">
                                <div id="popularPackagesChart" style="height: 100%;"></div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Unpaid Customers -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-3 h-100">
                    <div class="card-header card-header-clean py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <h5 class="mb-0 fw-bold text-dark me-2">
                                {{ __('dashboard.unpaid.title', ['count' => $limitMonths]) }}
                            </h5>
                            <span class="badge bg-danger-soft text-danger rounded-pill">
                                {{ count($unpaid3MonthsCustomers ?? []) }} {{ __('dashboard.unpaid.badge') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($unpaid3MonthsCustomers) || count($unpaid3MonthsCustomers) == 0)
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-check-circle text-success me-1"></i>
                                {{ __('dashboard.unpaid.empty') }}
                            </div>
                        @else
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light small text-muted sticky-top">
                                        <tr>
                                            <th class="ps-4">{{ __('dashboard.unpaid.customer_name') }}</th>
                                            @if ($isSuperAdmin)
                                                <th>{{ __('dashboard.unpaid.admin') }}</th>
                                            @endif
                                            <th class="text-center">{{ __('dashboard.unpaid.month_count') }}</th>
                                            <th class="text-end pe-4">{{ __('dashboard.unpaid.unpaid_period') }}
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        @foreach ($unpaid3MonthsCustomers as $customer)
                                            <tr>
                                                <td class="ps-4">
                                                    <a href="{{ route(
                                                        'billings.index',
                                                        array_filter([
                                                            'customer' => $customer->slug ?? null,
                                                            'search_name' => $customer->name,
                                                            'search_status' => 'BL',
                                                            'admin_name' => $customer->admin_name,
                                                        ]),
                                                    ) }}"
                                                        class="fw-bold text-dark text-decoration-none">
                                                        {{ $customer->name }}
                                                    </a>
                                                    @if ($customer->contact)
                                                        <div class="text-muted small">{{ $customer->contact }}</div>
                                                    @endif
                                                </td>
                                                @if ($isSuperAdmin)
                                                    <td>
                                                        <span
                                                            class="badge bg-light text-dark border">{{ $customer->admin_name ?? '-' }}</span>
                                                    </td>
                                                @endif
                                                <td class="text-center">
                                                    <span
                                                        class="badge {{ $customer->unpaid_months_count >= 3 ? 'bg-danger-soft text-danger' : 'bg-warning-soft text-warning' }} fw-bold">
                                                        {{ $customer->unpaid_months_count }}
                                                        {{ __('dashboard.unpaid.months_unit') }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="text-muted fw-bold">
                                                        {{ $customer->unpaid_periods }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
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
                                <h6 class="mb-0 fw-bold">
                                    <a href="{{ route('mikrotiks.show', $mikrotik->slug) }}"
                                        class="text-dark text-decoration-none">
                                        <i id="status-{{ $mikrotik->slug }}"
                                            class="fas fa-power-off text-danger me-2"></i>
                                        {{ $mikrotik->name }}
                                    </a>
                                </h6>
                            </div>
                            <div class="d-flex">
                                <span class="badge bg-light text-secondary border fw-normal me-1"
                                    id="boardname-{{ $mikrotik->slug }}">--</span>
                                <span class="badge bg-light text-secondary border fw-normal"
                                    id="uptime-{{ $mikrotik->slug }}">--</span>
                            </div>
                        </div>
                        <div class="card-body p-3 small">
                            <!-- CPU -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-microchip me-1"></i>
                                        {{ __('dashboard.router.cpu_usage') }}</span>
                                    <small id="cpu-{{ $mikrotik->slug }}" class="fw-bold text-dark">0%</small>
                                </div>
                                <div class="progress progress-soft" style="height: 6px;">
                                    <div id="cpu-bar-{{ $mikrotik->slug }}" class="progress-bar bg-primary"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- RAM -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-memory me-1"></i>
                                        {{ __('dashboard.router.ram_usage') }}</span>
                                    <small id="memory-{{ $mikrotik->slug }}"
                                        class="fw-bold text-dark">{{ __('dashboard.router.loading') }}</small>
                                </div>
                                <div class="progress progress-soft" style="height: 6px;">
                                    <div id="ram-bar-{{ $mikrotik->slug }}" class="progress-bar bg-warning"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <!-- HDD -->
                            <div>
                                <div class="d-flex justify-content-between text-muted mb-1">
                                    <span><i class="fas fa-hard-drive me-1"></i>
                                        {{ __('dashboard.router.disk_usage') }}</span>
                                    <small id="hdd-{{ $mikrotik->slug }}"
                                        class="fw-bold text-dark">{{ __('dashboard.router.loading') }}</small>
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

    @push('custom_scripts')
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
    @endpush

</x-layouts.app-layout>
