<x-layouts.app-layout title="Networks">

    @section('custom_styles')
        <link href="{{ asset('css/network-mgmt/data-show.css') }}" rel="stylesheet">
    @endsection

    <div class="container-fluid">
        <!-- OLT Header -->
        <div class="olt-header">
            <div class="olt-header-content">
                <div class="olt-icon">
                    <i class="fas fa-server"></i>
                </div>
                <div class="olt-info">
                    <h1 class="olt-title">{{ $olt->name }}</h1>
                    <div class="olt-meta">
                        <div class="olt-meta-item">
                            <i class="fas fa-network-wired text-secondary"></i>
                            <span class="text-body">{{ $olt->ip }}</span>
                        </div>
                        <div class="olt-meta-item">
                            <i class="fas fa-tag text-secondary"></i>
                            <span class="text-body">{{ $olt->vendor }}</span>
                        </div>
                    </div>
                    <div class="olt-actions">
                        <a href="{{ route('olts.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                        <button id="refresh-data-btn" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <a href="{{ route('olts.edit', $olt->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit OLT
                        </a>
                        <a href="/scan_olt/1228" class="btn btn-primary btn-sm scan-button">
                            <i class="fas fa-sync-alt"></i> Scan OLT
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="status-indicator">
                <i class="fas fa-history"></i> Last scan: {{ $lastUpdateOnus }}
            </div>
            <div id="scan-progress-container" class="progress-container mx-4 mb-3" style="display: none;">
                <div class="progress-label">
                    <span id="scan-progress-text">Initializing scan...</span>
                    <span id="scan-progress-percentage">0%</span>
                </div>
                <div class="progress-slim">
                    <div id="scan-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated"
                        role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                    </div>
                </div>
            </div>
        </div>

        <!-- ONU Summary Cards -->
        <div class="row summary-cards">
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card" data-status="total">
                    <div class="summary-icon border text-primary">
                        <i class="fas fa-network-wired"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" id="summary-total">{{ $totalOnus }}</div>
                        <div class="summary-label">Total ONUs</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card" data-status="online">
                    <div class="summary-icon border text-success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" id="summary-online">{{ $onlineOnus }}</div>
                        <div class="summary-label">Online</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card" data-status="offline">
                    <div class="summary-icon border text-danger">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" id="summary-offline">{{ $offlineOnus }}</div>
                        <div class="summary-label">Offline</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 mb-3">
                <div class="summary-card" data-status="other">
                    <div class="summary-icon border text-warning">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <div class="summary-content">
                        <div class="summary-value" id="summary-other">0</div>
                        <div class="summary-label">Other State</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ONU Data Table -->
        <div class="data-card">
            <div class="data-card-header">
                <h2 class="data-card-title">
                    <i class="fas fa-microchip"></i> ONU Devices
                </h2>
                <div class="timestamp-display">
                    <i class="fas fa-clock"></i> Last updated: {{ $lastUpdateOnus }}
                </div>
            </div>
            <div class="data-card-body">
                <div class="table-controls d-flex justify-content-between align-items-center mb-3">
                    <div class="show-entries">
                        <label>Show
                            <select class="form-select form-select-sm d-inline-block mx-1" style="width: 70px;">
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select> entries
                        </label>
                    </div>
                    <div class="search-box">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" class="form-control" placeholder="Search...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>INDEX</th>
                                <th>ONU NAME</th>
                                <th>STATE</th>
                                <th>RX POWER</th>
                                <th>PHYSICAL ID</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($onus as $onu)
                                <tr>
                                    {{-- ONU Index --}}
                                    <td>{{ $onu->onu_index }}</td>

                                    {{-- ONU Name --}}
                                    <td>{{ $onu->name }}</td>

                                    {{-- Status --}}
                                    <td>
                                        @if ($onu->status === 'Up')
                                            <span class="status-badge badge-online">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                Online
                                            </span>
                                        @else
                                            <span class="status-badge badge-offline">
                                                <i class="fas fa-circle me-1" style="font-size: 0.5rem;"></i>
                                                Offline
                                            </span>
                                        @endif
                                    </td>

                                    {{-- RX Power --}}
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div
                                                class="signal-indicator
                        @if ($onu->rx_power > -20) signal-good
                        @elseif($onu->rx_power > -27) signal-fair
                        @else signal-bad @endif">
                                                <i class="fas fa-signal"></i>
                                            </div>
                                            {{ $onu->rx_power }} dBm
                                        </div>
                                    </td>

                                    {{-- MAC Address --}}
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="font-monospace">{{ $onu->mac_address }}</span>
                                            <button class="btn btn-refresh" title="Update ONU data">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-footer d-flex justify-content-between align-items-center mt-3">
                    <div class="showing-entries">
                        Showing 1 to 10 of 58 entries
                    </div>
                    <nav>
                        <ul class="pagination pagination-sm">
                            <li class="page-item disabled">
                                <a class="page-link" href="#"><i class="fas fa-angle-left"></i></a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#"><i class="fas fa-angle-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <!-- Scan History -->
        <div class="scan-history-card">
            <div class="card-header">
                <h5><i class="fas fa-history me-2"></i> Scan History</h5>
                <button id="refresh-scan-logs" class="btn btn-sm btn-primary">
                    <i class="fas fa-sync-alt me-1"></i> Refresh
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-hover">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Status</th>
                                <th>ONUs Found</th>
                                <th>Duration</th>
                                <th>User</th>
                                <th>Message</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($histories as $history)
                                <tr>
                                    <td>{{ $history->updated_at }}</td>
                                    <td>
                                        @php
                                            $badgeClass = match ($history->status) {
                                                'Up' => 'bg-success text-white',
                                                'Down' => 'bg-danger text-white',
                                                default => 'bg-warning text-dark',
                                            };

                                            $status = match ($history->status) {
                                                'Up' => 'Success',
                                                'Down' => 'Failed',
                                                default => 'Undefined',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                    </td>
                                    <td>{{ $history->onu_count ?? '-' }}</td>
                                    <td>{{ $history->duration ?? '-' }}</td>
                                    <td>{{ $history->username ?? '-' }}</td>
                                    <td class="text-truncate" style="max-width: 300px;"
                                        title="{{ $history->details }}">
                                        {{ $history->details }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No scan history found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script src="{{ asset('js/network-mgmt/data-show.js') }}"></script>
    @endsection
</x-layouts.app-layout>
