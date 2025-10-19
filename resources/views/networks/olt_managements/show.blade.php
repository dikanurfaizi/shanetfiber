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
                        <button id="refresh-data-btn" data-olt-id="{{ $olt->id }}"
                            class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-sync-alt"></i> Refresh Data
                        </button>
                        <a href="{{ route('olts.edit', $olt->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit OLT
                        </a>
                        <a href="{{ route('olts.scanSingle', $olt->id) }}" class="btn btn-primary btn-sm scan-button"
                            data-olt-id="{{ $olt->id }}">
                            <i class="fas fa-sync-alt"></i> Scan OLT
                        </a>
                    </div>
                </div>
            </div>
            <hr>
            <div class="status-indicator">
                <i class="fas fa-history"></i> Last scan: {{ $lastUpdateOnts }}
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

        @php
            $currentStatus = request('status', 'total');
        @endphp

        <!-- ========================= -->
        <!-- OLT DETAIL DYNAMIC SECTION -->
        <!-- ========================= -->
        <div id="olt-details-container">
            @include('networks.partials.olts_details', [
                'currentStatus' => $currentStatus,
                'totalOnts' => $totalOnts,
                'onlineOnts' => $onlineOnts,
                'offlineOnts' => $offlineOnts,
                'otherOnts' => $otherOnts ?? 0,
                'lastUpdateOnts' => $lastUpdateOnts,
                'onts' => $onts,
                'ponGroups' => $ponGroups,
                'selectedPon' => $selectedPon,
            ])
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
                                <th>ONTs Found</th>
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
                                    <td>{{ $history->ont_count ?? '-' }}</td>
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
        <script>
            window.currentFilters = {
                pon_group: "{{ request('pon_group', $selectedPon ?? '') }}",
                perPage: "{{ request('perPage', 64) }}",
                status: "{{ request('status', 'total') }}"
            };
        </script>
        <script src="{{ asset('js/network-mgmt/data-show.js') }}"></script>
    @endsection
</x-layouts.app-layout>
