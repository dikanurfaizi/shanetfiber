{{-- OLT List Label --}}
<div class="mb-2">
    <h5 class="fw-bold" style="text-align: center">{{ __('OLT List') }}</h5>
</div>

@forelse ($olts as $olt)
    {{-- Mobile Card View --}}
    <tr class="olt-row olt-row-mobile d-table-row d-md-none mb-2" data-olt-id="{{ $olt->id }}"
        data-olt-name="{{ $olt->name }}">
        <td colspan="8">
            <div class="card mb-2 shadow-sm">
                <div class="card-body p-2">

                    {{-- Header: Name + Status --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="fw-bold text-truncate" style="max-width: 65%;">
                            {{ $olt->name }}
                        </div>

                        <div class="d-flex align-items-center gap-1 olt-status-indicator-wrapper">
                            @php
                                $status = strtolower($olt->status_text);
                            @endphp
                            <div class="olt-status-indicator {{ $status }}">
                                <i class="fas fa-circle"></i>
                            </div>
                            <span class="status-badge {{ $status }}">
                                {{ strtoupper($olt->status_text) }}
                            </span>
                        </div>
                    </div>

                    {{-- Body: Info --}}
                    <div class="d-flex flex-column gap-1 small">
                        <div><strong>{{ __('networks.table.type') }}:</strong> {{ $olt->vendor }}</div>
                        <div><strong>{{ __('networks.table.ip') }}:</strong> {{ $olt->ip }}</div>
                        <div><strong>{{ __('networks.table.port') }}:</strong> {{ $olt->port }}</div>
                        <div class="total-onus"><strong>{{ __('networks.table.onu') }}:</strong> {{ $olt->total_onu }}
                        </div>
                        <div>
                            <strong>{{ __('networks.table.last-scan') }}:</strong>
                            @php
                                $lastUpdate = optional($olt->latestUpdateLog)->updated_at
                                    ? \Carbon\Carbon::parse($olt->latestUpdateLog->updated_at)
                                    : null;
                            @endphp
                            @if ($lastUpdate)
                                {{ $lastUpdate->format('d/m/Y H:i') }}
                            @else
                                <span class="text-muted">Never</span>
                            @endif
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-primary w-100 dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            {{ __('networks.table.actions') }}
                        </button>
                        <ul class="dropdown-menu w-100">
                            @canany(['olt-view'])
                                <li><a class="dropdown-item"
                                        href="{{ route('olts.show', $olt->id) }}">{{ __('networks.table.view') }}</a></li>
                            @endcanany
                            @canany(['olt-scan'])
                                <li><a class="dropdown-item scan-olt-btn"
                                        href="{{ route('olts.scanSingle', $olt->id) }}">{{ __('networks.table.scan') }}</a>
                                </li>
                            @endcanany
                            @canany(['olt-check-health'])
                                <li><a class="dropdown-item check-snmp-status"
                                        href="{{ route('olts.healtCheckSingle', $olt->id) }}">{{ __('networks.table.health-check') }}</a>
                                </li>
                            @endcanany
                            @canany('olt-edit')
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item"
                                        href="{{ route('olts.edit', $olt->id) }}">{{ __('networks.table.edit') }}</a></li>
                            @endcanany
                            @canany('olt-delete')
                                <li>
                                    <button class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $olt->id }}, '{{ $olt->name }}', '{{ route('olts.destroy', $olt->id) }}')">{{ __('networks.table.delete') }}
                                    </button>
                                </li>
                            @endcanany
                        </ul>
                    </div>

                </div>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="text-center text-muted fw-bold">
            <div style="text-align: center">No OLT data found for this filter.</div>
        </td>
    </tr>
@endforelse
