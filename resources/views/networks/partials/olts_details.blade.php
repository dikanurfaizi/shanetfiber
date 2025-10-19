@php
    $currentStatus = $currentStatus ?? request('status', 'total');
@endphp

<!-- ONT Summary Cards -->
<div class="row summary-cards">
    <div class="col-lg-3 col-md-6 mb-3">
        <div class="summary-card {{ $currentStatus === 'total' ? 'active' : '' }}" data-status="total">
            <div class="summary-icon border text-primary">
                <i class="fas fa-network-wired"></i>
            </div>
            <div class="summary-content">
                <div class="summary-value" id="summary-total">{{ $totalOnts }}</div>
                <div class="summary-label">Total ONTs</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="summary-card {{ $currentStatus === 'online' ? 'active' : '' }}" data-status="online">
            <div class="summary-icon border text-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="summary-content">
                <div class="summary-value" id="summary-online">{{ $onlineOnts }}</div>
                <div class="summary-label">Online</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="summary-card {{ $currentStatus === 'offline' ? 'active' : '' }}" data-status="offline">
            <div class="summary-icon border text-danger">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="summary-content">
                <div class="summary-value" id="summary-offline">{{ $offlineOnts }}</div>
                <div class="summary-label">Offline</div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 mb-3">
        <div class="summary-card {{ $currentStatus === 'other' ? 'active' : '' }}" data-status="other">
            <div class="summary-icon border text-warning">
                <i class="fas fa-question-circle"></i>
            </div>
            <div class="summary-content">
                <div class="summary-value" id="summary-other">{{ $otherOnts ?? 0 }}</div>
                <div class="summary-label">Other State</div>
            </div>
        </div>
    </div>
</div>

<!-- ONT Data Table -->
<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">
            <i class="fas fa-microchip"></i> ONT Devices
        </h2>
        <div class="timestamp-display">
            <i class="fas fa-clock"></i> Last updated: {{ $lastUpdateOnts }}
        </div>
    </div>

    <div class="data-card-body">
        {{-- PON Group Buttons --}}
        @if ($ponGroups && count($ponGroups) > 0)
            <div class="pon-group-buttons mb-3 d-flex flex-wrap gap-2">
                @foreach ($ponGroups as $group)
                    @php
                        $isActive = request('pon_group', $selectedPon ?? 1) == $group;
                    @endphp
                    <a href="{{ request()->fullUrlWithQuery(['pon_group' => $group]) }}"
                        class="mr-2 btn btn-sm {{ $isActive ? 'btn-primary' : 'btn-outline-primary' }}">
                        <i class="fas fa-network-wired me-1"></i> PON {{ $group }}
                    </a>
                @endforeach
            </div>
        @endif

        <div class="table-responsive ont-table-wrapper d-none d-md-block">
            <table class="table table-hover ont-table">
                <thead>
                    <tr>
                        <th>INDEX</th>
                        <th>ONT NAME</th>
                        <th>STATE</th>
                        <th>RX POWER</th>
                        <th>TEMPERATURE</th>
                        <th>MAC ADDRESS</th>
                        <th>IP ADDRESS</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($onts as $ont)
                        <tr>
                            <td class="text-primary">{{ $ont->ont_index }}</td>
                            <td>
                                @if (!empty($ont->slug))
                                    <a href="{{ route('customers.show', $ont->slug) }}">{{ $ont->customer_name }}</a>
                                @elseif(!empty($ont->customer_id))
                                    <a
                                        href="{{ route('customers.show', $ont->customer_id) }}">{{ $ont->customer_name }}</a>
                                @else
                                    <span class="text-muted">{{ $ont->customer_name ?? 'Unknown Customer' }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($ont->ont_status === 'Up')
                                    <span class="status-badge badge-online"><i class="fas fa-circle me-1"></i>
                                        Online</span>
                                @else
                                    <span class="status-badge badge-offline"><i class="fas fa-circle me-1"></i>
                                        Offline</span>
                                @endif
                            </td>
                            <td>
                                <div class="{{ $ont->signal_class }}">
                                    <i class="fas fa-signal"></i><span>{{ $ont->ont_rx_power }} dBm</span>
                                </div>
                            </td>
                            <td>{{ $ont->ont_temp }}</td>
                            <td>{{ $ont->ont_mac }}</td>
                            <td>
                                @if (!empty($ont->customer_ip_address))
                                    {{ $ont->customer_ip_address }}
                                    <a class="badge bg-primary text-white ms-2"
                                        href="http://{{ $ont->customer_ip_address }}:{{ $ont->port ?? '80' }}"
                                        target="_blank">Remote</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No ONT data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Mobile Card View -->
        <div class="ont-cards d-md-none">
            @foreach ($onts as $ont)
                <div class="ont-card">
                    <div class="ont-card-header d-flex justify-content-between align-items-center">
                        <strong class="text-primary">#{{ $ont->ont_index }}</strong>

                        <div class="d-flex align-items-center gap-1">
                            @if ($ont->ont_status === 'Up')
                                <span class="status-badge badge-online"><i class="fas fa-circle me-1"></i> Online</span>
                            @else
                                <span class="status-badge badge-offline"><i class="fas fa-circle me-1"></i>
                                    Offline</span>
                            @endif
                        </div>
                    </div>
                    <div class="ont-card-body">
                        <div>
                            @if (!empty($ont->slug))
                                <a href="{{ route('customers.show', $ont->slug) }}">{{ $ont->customer_name }}</a>
                            @elseif(!empty($ont->customer_id))
                                <a
                                    href="{{ route('customers.show', $ont->customer_id) }}">{{ $ont->customer_name }}</a>
                            @else
                                <span class="text-muted" title="Customer record not linked">
                                    {{ $ont->customer_name ?? 'Unknown Customer' }}
                                </span>
                            @endif
                        </div>
                        <div
                            class="@if ($ont->ont_rx_power >= -15 && $ont->ont_rx_power <= -1) signal-too-strong
                                    @elseif($ont->ont_rx_power > -24 && $ont->ont_rx_power < -15)
                                        signal-good
                                    @elseif($ont->ont_rx_power > -25 && $ont->ont_rx_power <= -24)
                                        signal-low
                                    @elseif($ont->ont_rx_power >= -30 && $ont->ont_rx_power <= -25)
                                        signal-too-low @endif">
                            <i class="fas fa-signal me-1"></i>
                            <strong>RX Power:</strong> {{ $ont->ont_rx_power }} dBm
                        </div>
                        <div><strong>Temp:</strong> {{ $ont->ont_temp }}</div>
                        <div><strong>MAC:</strong> {{ $ont->ont_mac }}</div>
                        <div>
                            <strong>IP:</strong>
                            @if (!empty($ont->customer_ip_address))
                                {{ $ont->customer_ip_address }}
                                <a class="badge bg-primary text-white ms-2"
                                    href="http://{{ $ont->customer_ip_address }}:{{ $ont->port ?? '80' }}"
                                    target="_blank" title="Open ONU Web Management">
                                    Remote
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="table-footer d-flex justify-content-between align-items-center mt-3">
            <div class="showing-entries">
                Showing {{ $onts->firstItem() }} to {{ $onts->lastItem() }} of {{ $onts->total() }} entries
            </div>
            <nav>{{ $onts->links('pagination::default') }}</nav>
        </div>
    </div>
</div>
