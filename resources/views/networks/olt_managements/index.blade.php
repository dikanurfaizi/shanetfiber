<x-layouts.app-layout title="Networks">

    @section('custom_styles')
        <link href="{{ asset('css/network-mgmt/dashboard.css') }}" rel="stylesheet">
    @endsection

    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="m-0 font-weight-bold text-primary mb-2">
            {{ __('networks.header.olt-index') }}
        </h5>
        <a href="{{ route('olts.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus-circle me-1"></i> {{ __('networks.header.add-olt') }}
        </a>
    </div>

    <div class="container-fluid py-4">
        <!-- OLT Statistics Summary -->
        <div class="stats-overview">
            <div class="dashboard-section-header">
                <h2 class="h4 fw-bold text-gray-800">
                    <span
                        class="bg-primary-light text-primary rounded p-2 mr-2 d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    {{ __('networks.title.olt-statistics') }}
                </h2>
                <div class="d-flex align-items-center flex-wrap small">
                    <div class="last-check-time me-3 mb-2 mb-md-0">
                        <small class="text-muted">{{ __('networks.label.last-status-check') }} </small>
                        <small class="text-muted" id="last-check">
                            {{ $lastStatusCheck }}</small>
                    </div>
                    <button id="batch-status-check" class="btn btn-sm btn-outline-primary me-2 mb-2 mb-md-0">
                        <i class="fas fa-sync-alt me-1"></i> {{ __('networks.button.check-all-status') }}
                    </button>
                    <button id="batch-scan" class="btn btn-sm btn-outline-success me-3 mb-2 mb-md-0">
                        <i class="fas fa-search me-1"></i> {{ __('networks.button.scan-all-olt') }}
                    </button>
                    <div
                        class="last-update-badge d-inline-flex align-items-center px-3 py-1 rounded-pill bg-primary-light text-primary small">
                        <i class="fas fa-clock mr-1"></i>
                        {{ __('networks.label.last-updated') }} <span id="current-time" class="ms-1 ml-1">
                            {{ $lastUpdate }}</span>
                    </div>
                </div>
            </div>

            <!-- OLT Stats Cards -->
            <div class="slim-stats-row">
                <!-- Total OLTs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-primary-light text-primary">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="total-olts" class="slim-stats-value">{{ $totalOlts }}</div>
                        <div class="slim-stats-label">
                            {{ __('networks.label.total-olts') }}
                            <span class="personal-data-badge" title="Only shows OLTs you have permission to view">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Online OLTs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-success-light text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="online-olts" class="slim-stats-value">{{ $activeOlts }}</div>
                        <div class="slim-stats-label">
                            {{ __('networks.label.active-olts') }}
                            <span class="personal-data-badge" title="Only shows OLTs you have permission to view">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                        <div class="slim-stats-progress">
                            <div id="online-olts-bar" class="slim-stats-bar bg-success" style="width: 100.0%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Total ONUs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-info-light text-info">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="total-onus" class="slim-stats-value">{{ $totalOnts }}</div>
                        <div class="slim-stats-label">
                            {{ __('networks.label.total-onus') }}
                            <span class="personal-data-badge"
                                title="Only shows ONUs from OLTs you have permission to view">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Scanning OLTs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-warning-light bg-opacity-10 text-warning">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="scanning-count" class="slim-stats-value">0</div>
                        <div class="slim-stats-label">
                            {{ __('networks.label.scanning') }}
                            <span class="personal-data-badge" title="Only shows OLTs you have permission to view">
                                <i class="fas fa-user"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Batch Check Progress Bar -->
            <input type="hidden" id="scan-job-id" value="">

            <div id="batch-check-progress-container" class="mt-3 d-none">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <div id="batch-check-status">
                        Checking OLT <span id="batch-check-current">0</span>/<span id="batch-check-total">0</span>
                    </div>
                    <div id="batch-check-percentage">0%</div>
                </div>
                <div class="progress" style="height: 10px;">
                    <div id="batch-check-progress-bar" class="progress-bar progress-bar-striped" role="progressbar"
                        style="width: 0%;"></div>
                </div>
                <div id="batch-check-current-olt" class="small text-muted mt-1">-</div>
            </div>
        </div>

        <!-- OLT Search and Filter Section -->
        <div class="filter-section mt-4">
            <div class="card">
                <div class="card-body">
                    <!-- Toggle button (muncul hanya di mobile) -->
                    <div class="d-md-none mb-3 text-end">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterFormCollapse">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>

                    <!-- Filter Form -->
                    <div class="collapse d-md-block" id="filterFormCollapse">
                        <form id="filter-form" action="{{ route('olts.filter') }}" method="POST" class="row g-3">
                            @csrf
                            <!-- Search by OLT Name/IP -->
                            <div class="col-md-3 mb-3">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                                    <input type="text" class="form-control" id="search-input" name="search"
                                        placeholder="{{ __('networks.placeholder.search') }}" value="">
                                </div>
                            </div>

                            <!-- Filter by OLT Type -->
                            <div class="col-md-2 mb-3">
                                {!! Form::select('olt_type', ['' => 'OLT Types'] + $oidMappings->toArray(), null, [
                                    'class' => 'form-control',
                                    'id' => 'type-filter',
                                ]) !!}
                            </div>

                            <!-- Filter by Status -->
                            <div class="col-md-2 mb-3">
                                <select class="form-control" id="status-filter" name="status">
                                    <option value="">{{ __('networks.placeholder.all-status') }}</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="Active">Active</option>
                                    <option value="Scanning">Scanning</option>
                                </select>
                            </div>

                            <!-- Show Entries -->
                            <div class="col-md-2 mb-3">
                                <select class="form-control" id="entries-filter" name="entries">
                                    <option value="25" selected>25 entries</option>
                                    <option value="50">50 entries</option>
                                    <option value="100">100 entries</option>
                                    <option value="200">200 entries</option>
                                    <option value="500">500 entries</option>
                                    <option value="1000">1000 entries</option>
                                </select>
                            </div>

                            <!-- Apply Filters Button -->
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <button type="submit" class="btn btn-primary me-2 flex-grow-1">
                                        <i class="fas fa-filter me-2"></i> {{ __('networks.button.filter') }}
                                    </button>
                                    <a href="javascript:void(0)" id="rescan-olts"
                                        class="btn btn-outline-secondary ml-1">
                                        <i class="fas fa-sync-alt"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div id="olts-summary-wrapper">
                        @include('networks.partials.olts_summary', ['olts' => $olts])
                    </div>

                </div>
            </div>
        </div>

        <!-- OLT List Section -->
        <div class="olt-list-section mt-4">
            <!-- OLT Table -->
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">

                        <!-- Desktop Table -->
                        <table class="table olt-table table-hover d-none d-md-table" id="olt-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="status-cell">{{ __('networks.table.status') }}</th>
                                    <th scope="col">{{ __('networks.table.olt-name') }}</th>
                                    <th scope="col" class="d-none-xs">{{ __('networks.table.type') }}</th>
                                    <th scope="col">{{ __('networks.table.ip') }}</th>
                                    <th scope="col" class="d-none-xs">{{ __('networks.table.port') }}</th>
                                    <th scope="col">{{ __('networks.table.onu') }}</th>
                                    <th scope="col" class="last-scan-cell">{{ __('networks.table.last-scan') }}
                                    </th>
                                    <th scope="col" class="text-center">{{ __('networks.table.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody id="olt-table-body">
                                @include('networks.partials.olts_table_desktop', ['olts' => $olts])
                            </tbody>
                        </table>

                        <!-- Mobile Card List -->
                        <div id="olt-card-body" class="d-block d-md-none">
                            @include('networks.partials.olts_table_mobile', ['olts' => $olts])
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('custom_scripts')
        <script src="{{ asset('js/network-mgmt/dashboard.js') }}"></script>
    @endsection
</x-layouts.app-layout>
