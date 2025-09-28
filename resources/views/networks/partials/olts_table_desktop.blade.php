@forelse ($olts as $olt)
    <tr class="olt-row olt-row-desktop d-none d-md-table-row" data-olt-id="{{ $olt->id }}"
        data-olt-name="{{ $olt->name }}">
        {{-- Desktop Table Row --}}
        <td class="status-cell">
            <div class="d-flex align-items-center">
                <div class="olt-status-indicator {{ strtolower($olt->display_status) }}">
                    @if (strtolower($olt->display_status) === 'active')
                        <i class="fas fa-check-circle text-success"></i>
                    @elseif (strtolower($olt->display_status) === 'inactive')
                        <i class="fas fa-times-circle text-danger"></i>
                    @elseif (strtolower($olt->display_status) === 'scanning')
                        <i class="fas fa-sync-alt text-warning"></i>
                    @endif
                </div>
                <span class="badge status-badge {{ strtolower($olt->display_status) }}">
                    {{ strtoupper($olt->display_status) }}
                </span>
            </div>
        </td>
        <td>{{ $olt->name }}</td>
        <td class="d-none-xs"><span class="badge type-badge">{{ $olt->vendor }}</span></td>
        <td><span class="font-monospace text-info">{{ $olt->ip }}</span>
            <div class="d-md-none"> <small class="text-muted">{{ $olt->vendor }}</small> </div>
        </td>
        <td class="d-none-xs"><span class="font-monospace">{{ $olt->port }}</span></td>
        <td><span class="badge onu-badge">{{ $olt->total_onu }}</span>
            <div class="d-md-none"> <small class="text-muted">Port: {{ $olt->port }}</small> </div>
        </td>
        <td>
            @php
                $lastUpdate = optional($olt->latestUpdateLog)->updated_at
                    ? \Carbon\Carbon::parse($olt->latestUpdateLog->updated_at)
                    : null;
            @endphp
            @if ($lastUpdate)
                <span class="last-scan-time" title="{{ $lastUpdate->format('F d, Y \a\t h:i A') }}">
                    {{ $lastUpdate->format('H:i') }}
                </span>
                <span class="last-scan-date">{{ $lastUpdate->format('d/m/Y') }}</span>
            @else
                <span class="text-muted">Never</span>
            @endif
        </td>
        <td class="text-center">
            <div class="dropdown">
                <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-1"></i> {{ __('networks.table.actions') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('olts.show', $olt->id) }}"><i
                                class="fas fa-eye text-primary"></i> {{ __('networks.table.view') }}</a></li>
                    <li>
                        <a href="{{ route('olt.scanSingle', $olt->id) }}" class="dropdown-item scan-olt-btn"
                            data-olt-id="{{ $olt->id }}">
                            <i class="fas fa-sync-alt text-success"></i> {{ __('networks.table.scan') }}
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('olt.healtCheckSingle', $olt->id) }}" class="dropdown-item check-snmp-status"
                            data-olt-id="{{ $olt->id }}">
                            <i class="fas fa-heartbeat text-info"></i> {{ __('networks.table.health-check') }}
                        </a>
                    </li>
                    <hr class="dropdown-divider">
                    </li>
                    <li><a class="dropdown-item" href="{{ route('olts.edit', $olt->id) }}"><i
                                class="fas fa-edit text-warning"></i> {{ __('networks.table.edit') }}</a></li>
                    <li>
                        <button class="dropdown-item text-danger"
                            onclick="confirmDelete({{ $olt->id }}, '{{ $olt->name }}', '{{ route('olts.destroy', $olt->id) }}')">
                            <i class="fas fa-trash"></i> {{ __('networks.table.delete') }}
                        </button>
                    </li>
                </ul>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted">
            <p style="tab-align: center">No OLT data found for this filter.</p>
        </td>
    </tr>
@endforelse
