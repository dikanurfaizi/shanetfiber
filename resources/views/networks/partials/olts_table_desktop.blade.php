@forelse ($olts as $olt)
    <tr class="olt-row olt-row-desktop d-none d-md-table-row" data-olt-id="{{ $olt->id }}"
        data-olt-name="{{ $olt->name }}">
        {{-- Desktop Table Row --}}
        <td class="status-cell">
            <div class="d-flex align-items-center">
                <div class="olt-status-indicator {{ strtolower($olt->status_text) }}">
                    @if (strtolower($olt->status_text) === 'active')
                        <i class="fas fa-check-circle text-success"></i>
                    @elseif (strtolower($olt->status_text) === 'inactive')
                        <i class="fas fa-times-circle text-danger"></i>
                    @elseif (strtolower($olt->status_text) === 'scanning')
                        <i class="fas fa-sync-alt text-warning"></i>
                    @endif
                </div>
                <span class="badge status-badge {{ strtolower($olt->status_text) }}">
                    {{ strtoupper($olt->status_text) }}
                </span>
            </div>
        </td>
        <td>{{ $olt->name }}</td>
        <td class="d-none-xs"><span class="badge type-badge">{{ $olt->vendor }}</span></td>
        <td><span class="font-monospace text-info">{{ $olt->ip }}</span>
            <div class="d-md-none"> <small class="text-muted">{{ $olt->vendor }}</small> </div>
        </td>
        <td class="d-none-xs"><span class="font-monospace">{{ $olt->port }}</span></td>
        <td class="total-onus"><span class="badge onu-badge">{{ $olt->total_ont }}</span>
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
            @canany(['olt-view'])
            <!-- Tombol View di luar dropdown -->
            <a class="btn btn-sm btn-primary me-1" href="{{ route('olts.show', $olt->id) }}">
                <i class="fas fa-eye"></i> {{ __('networks.table.view') }}
            </a>
            @endcanany

            <!-- Dropdown action -->
            <div class="dropdown d-inline">
                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-cog me-1"></i> {{ __('networks.table.actions') }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @canany(['olt-scan'])
                    <li>
                        <a href="{{ route('olts.scanSingle', $olt->id) }}" class="dropdown-item scan-olt-btn"
                            data-olt-id="{{ $olt->id }}">
                            <i class="fas fa-sync-alt text-success"></i> {{ __('networks.table.scan') }}
                        </a>
                    </li>
                    @endcanany
                    @canany(['olt-check-health'])
                    <li>
                        <a href="{{ route('olts.healtCheckSingle', $olt->id) }}" class="dropdown-item check-snmp-status"
                            data-olt-id="{{ $olt->id }}">
                            <i class="fas fa-heartbeat text-info"></i> {{ __('networks.table.health-check') }}
                        </a>
                    </li>
                    @endcanany
                    @canany('olt-edit')
                    <hr class="dropdown-divider">
                    <li>
                        <a class="dropdown-item" href="{{ route('olts.edit', $olt->id) }}">
                            <i class="fas fa-edit text-warning"></i> {{ __('networks.table.edit') }}
                        </a>
                    </li>
                    @endcanany
                    @canany('olt-delete')
                    <li>
                        <button class="dropdown-item text-danger"
                            onclick="confirmDelete({{ $olt->id }}, '{{ $olt->name }}', '{{ route('olts.destroy', $olt->id) }}')">
                            <i class="fas fa-trash"></i> {{ __('networks.table.delete') }}
                        </button>
                    </li>
                    @endcanany
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
