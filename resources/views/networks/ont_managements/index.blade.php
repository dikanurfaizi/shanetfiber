<x-layouts.app-layout title="Networks">

    @section('custom_styles')
        <link href="{{ asset('css/network-mgmt/ont-dashboard.css') }}" rel="stylesheet">
    @endsection

    <!-- Page Header -->
    <div class="card-header py-3 d-flex align-items-center">
        <h5 class="m-0 fw-bold text-primary mb-2">
            {{ __('networks.header.ont-index') }}
        </h5>
    </div>

    <div class="container-fluid py-4">
        <p class="page-description">{{ __('networks.label.ont-info') }}</p>
        <!-- Filter ONUs Section -->
        <div class="filter-section mt-4">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h4 class="fw-bolder mb-4 text-dark">{{ __('networks.label.filter-ont') }}</h4>

                    <!-- Toggle button (hanya muncul di mobile) -->
                    <div class="d-md-none mb-3 text-end">
                        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#filterOnuCollapse">
                            <i class="fas fa-filter"></i> Filters
                        </button>
                    </div>

                    <!-- Filter Form -->
                    <div class="collapse d-md-block" id="filterOnuCollapse">
                        <form id="filter-form" action="" method="GET" class="row g-3 align-items-end">
                            @csrf

                            <!-- Status -->
                            <div class="col-md-3 mb-3">
                                <label for="status-filter" class="form-label fw-semibold text-muted mb-1">Status</label>
                                <select name="status" class="form-control" id="status-filter">
                                    @foreach (['' => 'All Statuses', 'online' => 'Online', 'offline' => 'Offline', 'other' => 'Other'] as $k => $v)
                                        <option value="{{ $k }}" {{ request('status') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- OLT -->
                            <div class="col-md-3 mb-3">
                                <label for="olt-filter" class="form-label fw-semibold text-muted mb-1">OLT</label>
                                <select name="olt_id" class="form-control" id="olt-filter">
                                    @foreach (['' => 'All OLTs'] + $olts->toArray() as $k => $v)
                                        <option value="{{ $k }}" {{ request('olt_id') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Rx Power -->
                            <div class="col-md-3 mb-3">
                                <label for="rxpower-filter" class="form-label fw-semibold text-muted mb-1">Rx
                                    Power</label>
                                <select name="signal_id" class="form-control" id="rxpower-filter">
                                    @foreach (['' => 'All Rx Power'] + $signal->toArray() as $k => $v)
                                        <option value="{{ $k }}" {{ request('signal_id') == $k ? 'selected' : '' }}>{{ $v }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-3 d-flex gap-2 mb-3">
                                <button type="submit" class="btn btn-primary flex-fill me-2">
                                    <i class="fas fa-filter me-2"></i> Apply Filters
                                </button>
                                <a href="{{ route('onts.index') }}" class="btn btn-outline-secondary flex-fill">
                                    <i class="fas fa-times me-2"></i> Reset
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Container -->
        <div class="alert-container"></div>

        <!-- OLT Statistics Summary -->
        <div class="stats-overview">
            <!-- ONU Stats Cards -->
            <div class="slim-stats-row">

                <!-- Total ONUs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-primary-light text-primary">
                        <i class="fas fa-server"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="total-olts" class="slim-stats-value">{{ $summary->total ?? 0 }}</div>
                        <div class="slim-stats-label" title="Only shows OLTs you have permission to view">
                            {{ __('networks.label.total-onts') }}
                        </div>
                    </div>
                </div>

                <!-- Online ONUs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-success-light text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="online-olts" class="slim-stats-value">{{ $summary->online ?? 0 }}</div>
                        <div class="slim-stats-label" title="Only shows OLTs you have permission to view">
                            {{ __('networks.label.active-onts') }}
                        </div>
                        <div class="slim-stats-progress">
                            <div id="online-olts-bar" class="slim-stats-bar bg-success" style="width: 100.0%;"></div>
                        </div>
                    </div>
                </div>

                <!-- Offline ONUs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-danger-light text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="total-onus" class="slim-stats-value">{{ $summary->offline ?? 0 }}</div>
                        <div class="slim-stats-label" title="Only shows OLTs you have permission to view">
                            {{ __('networks.label.offline-onts') }}
                        </div>
                    </div>
                </div>

                <!-- Other State ONUs -->
                <div class="slim-stats-card">
                    <div class="slim-stats-icon bg-warning-light text-warning">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="slim-stats-info">
                        <div id="scanning-count" class="slim-stats-value">{{ $summary->other ?? 0 }}</div>
                        <div class="slim-stats-label" title="Only shows OLTs you have permission to view">
                            {{ __('networks.label.other-onts-status') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ONU Data Table -->
        <div class="user-section">
            <!-- Search and Refresh Controls - Always visible -->
            <div class="user-body mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">

                    <!-- Search Form -->
                    <form id="search-form" action="{{ route('onts.index') }}" method="get"
                        class="flex-grow-1 d-flex align-items-center">
                        <div class="input-group search-group">
                            <span class="input-group-text bg-white border-end-0 ps-3">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 border-end-0" id="onu-search"
                                name="search" placeholder="Search ONT name, ID, or Username..."
                                value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary btn-search-submit px-3">
                                <i class="fas fa-arrow-right d-md-none"></i>
                                <span class="d-none d-md-inline">Search</span>
                            </button>
                        </div>
                    </form>

                    <!-- Refresh Button (Desktop) -->
                    <a id="refresh-data-btn" class="btn btn-outline-primary refresh-btn d-none d-md-inline-flex"
                        href="{{ route('onts.index') }}" title="Refresh Data">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </a>
                </div>
            </div>

            <!-- Sticky Refresh Button for Mobile -->
            <div class="d-md-none sticky-refresh-container text-center">
                <a href="{{ route('onts.index') }}" class="btn btn-outline-primary w-100 py-2">
                    <i class="fas fa-sync-alt me-1"></i> Refresh ONT Data
                </a>
            </div>

            <div class="user-body">
                <div class="table-responsive">
                    <div id="onu-table_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <table class="table table-hover table-striped dataTable no-footer" id="onu-table">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="sorting sorting_asc" tabindex="0" aria-controls="onu-table"
                                        rowspan="1" colspan="1" aria-sort="ascending"
                                        aria-label="OLT: activate to sort column descending" style="width: 110.25px;">
                                        OLT
                                    </th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="Index: activate to sort column ascending"
                                        style="width: 52.9125px;">Index</th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="ONU Name: activate to sort column ascending"
                                        style="width: 261.125px;">ONU Name</th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="IP Address: activate to sort column ascending"
                                        style="width: 261.125px;">IP Address</th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="State: activate to sort column ascending"
                                        style="width: 86.225px;">State</th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="Rx Power: activate to sort column ascending"
                                        style="width: 139.225px;">Rx Power</th>
                                    <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                        colspan="1" aria-label="Physical ID: activate to sort column ascending"
                                        style="width: 205.462px;">Physical ID</th>
                                    @canany('ont-reboot')
                                        <th class="sorting" tabindex="0" aria-controls="onu-table" rowspan="1"
                                            colspan="1" aria-label="Reboot: activate to sort column ascending"
                                            style="width: 5px;">Reboot</th>
                                    @endcan
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($onts as $ont)
                                    <tr>
                                        <td data-label="OLT">
                                            <a href="/view_olt_by_id/{{ $ont->olt_id }}"
                                                class="text-decoration-none">
                                                {{ $ont->olt_name }}
                                            </a>
                                        </td>

                                        <td data-label="Index">{{ $ont->ont_index }}</td>

                                        <td data-label="ONU Name" class="fw-medium">
                                            @if (!empty($ont->customer_slug))
                                                <a href="{{ route('customers.show', $ont->slug) }}">
                                                    {{ $ont->customer_name }}
                                                </a>
                                            @elseif(!empty($ont->customer_id))
                                                <a href="{{ route('customers.show', $ont->customer_id) }}">
                                                    {{ $ont->customer_name }}
                                                </a>
                                            @else
                                                {{ $ont->customer_name ?? 'Unknown Customer' }}
                                            @endif
                                        </td>

                                        <td data-label="IP Address" class="fw-medium">
                                            @if (!empty($ont->customer_ip))
                                                {{ $ont->customer_ip }}
                                                <a class="badge bg-primary text-white ms-2"
                                                    href="http://{{ $ont->customer_ip }}:{{ $ont->port ?? '80' }}"
                                                    target="_blank">
                                                    Remote
                                                </a>
                                            @else
                                                N/A
                                            @endif
                                        </td>

                                        <td data-label="State">
                                            @if ($ont->ont_status === 'Up')
                                                <span class="status-badge badge-online">
                                                    <i class="fas fa-circle me-1"></i> Online
                                                </span>
                                            @else
                                                <span class="status-badge badge-offline">
                                                    <i class="fas fa-circle me-1"></i> Offline
                                                </span>
                                            @endif
                                        </td>

                                        <td data-label="Rx Power">
                                            <div class="{{ $ont->signal_class }}">
                                                <i class="fas fa-signal"></i>
                                                <span>{{ $ont->ont_rx_power }} dBm</span>
                                            </div>
                                        </td>

                                        <td data-label="Physical ID">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="font-monospace">{{ $ont->customer_mac }}</span>
                                            </div>
                                        </td>

                                        @canany('ont-reboot')
                                            <td data-label="Reboot">
                                                <button class="btn btn-danger btn-reboot reboot-onu-btn"
                                                    data-olt-id="{{ $ont->olt_id }}"
                                                    data-onu-index="{{ $ont->ont_index }}"
                                                    title="Reboot {{ $ont->customer_name ?? $ont->ont_index }}">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </td>
                                        @endcanany
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pagination Section -->
                <div class="pagination-wrapper d-flex justify-content-between align-items-center mt-4 flex-wrap">
                    <div class="entries-info text-muted mb-2 mb-md-0">
                        Showing {{ $onts->firstItem() }} to {{ $onts->lastItem() }} of {{ $onts->total() }} entries
                    </div>
                    <nav class="pagination-nav">
                        {{ $onts->appends(request()->query())->links('pagination::default') }}
                    </nav>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script src="{{ asset('js/network-mgmt/ont-dashboard.js') }}"></script>
    @endsection
</x-layouts.app-layout>
