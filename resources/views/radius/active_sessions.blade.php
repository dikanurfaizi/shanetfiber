<x-layouts.cpanel.settings.app-layout :title="__('radius.active_sessions.title')">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary mb-2">
            {{ __('radius.active_sessions.header') }}
        </h5>

        <span>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-1"></i> {{ $errors->first() }}
                    <button type="button" class="close" data-bs-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </span>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                <span class="badge bg-primary text-white p-2">
                    <i class="fas fa-users me-1"></i> {{ __('radius.active_sessions.total_online') }}:
                    {{ $activeSessions->count() }}
                </span>
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Tombol Refresh Session --}}
                <a href="{{ route('radius.active-sessions') }}"
                    class="btn btn-secondary btn-sm d-flex align-items-center" style="height: 35px;"
                    title="{{ __('radius.index.btn_refresh_title') }}">
                    <i class="fas fa-sync-alt me-1"></i> {{ __('radius.index.btn_refresh') }}
                </a>
            </div>
        </div>

        {{-- Desktop View Table --}}
        <div class="d-none d-md-block">
            <div style="overflow-x: auto;">
                <table id="activeSessionsTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="table-secondary text-center">
                            <th><span class="badge bg-danger-soft text-primary">{{ __('radius.table.no') }}</span></th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.pppoe_username') }}</span>
                            </th>
                            <th><span class="badge bg-danger-soft text-primary">{{ __('radius.table.nas_ip') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.start_time') }}</span>
                            </th>
                            <th><span class="badge bg-danger-soft text-primary">{{ __('radius.table.uptime') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.bandwidth_usage') }}</span>
                            </th>
                            <th width="100px"><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.action') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($activeSessions as $key => $session)
                            @php
                                $totalBytes = ($session->acctinputoctets ?? 0) + ($session->acctoutputoctets ?? 0);
                                $totalMb = round($totalBytes / 1024 / 1024, 2);

                                // Format Uptime Sederhana (Detik ke Jam/Menit)
                                $seconds = $session->acctsessiontime ?? 0;
                                $hours = floor($seconds / 3600);
                                $minutes = floor(($seconds / 60) % 60);
                                $uptimeFormatted = "{$hours}h {$minutes}m";
                            @endphp
                            <tr class="text-center align-middle">
                                <td><span class="badge bg-danger-soft">{{ $loop->iteration }}</span></td>
                                <td class="fw-bold text-start">
                                    <span
                                        class="badge bg-light text-dark font-monospace">{{ $session->username }}</span>
                                </td>
                                <td><small class="text-muted font-monospace">{{ $session->nasipaddress }}</small></td>
                                <td><small class="text-muted">{{ $session->acctstarttime }}</small></td>
                                <td><span class="badge bg-info-soft text-info">{{ $uptimeFormatted }}</span></td>
                                <td><span class="badge bg-turquoise-soft text-turquoise">{{ $totalMb }} MB</span>
                                </td>
                                <td class="text-center">
                                    <form id="disconnectForm-{{ $loop->iteration }}" method="POST"
                                        action="{{ route('radius.customers.disconnect', $session->username) }}">
                                        @csrf
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="confirmDisconnect('{{ $session->username }}', 'disconnectForm-{{ $loop->iteration }}')"
                                            title="{{ __('radius.action.disconnect') }}">
                                            <i class="fas fa-power-off me-1"></i> {{ __('radius.action.kick') }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ __('radius.active_sessions.no_active_sessions') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile View Cards --}}
        <div class="d-md-none">
            @forelse ($activeSessions as $key => $session)
                @php
                    $totalBytes = ($session->acctinputoctets ?? 0) + ($session->acctoutputoctets ?? 0);
                    $totalMb = round($totalBytes / 1024 / 1024, 2);

                    $seconds = $session->acctsessiontime ?? 0;
                    $hours = floor($seconds / 3600);
                    $minutes = floor(($seconds / 60) % 60);
                    $uptimeFormatted = "{$hours}h {$minutes}m";
                @endphp
                <div class="card mb-3 shadow-sm border text-start">
                    <div class="card-body p-2">

                        {{-- Baris 1: No & Status Active Badge --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="badge rounded-pill bg-secondary px-2 py-1">
                                #{{ $loop->iteration }}
                            </span>
                            <span class="badge text-white bg-success py-1">
                                <i class="fas fa-wifi me-1"></i> ONLINE
                            </span>
                        </div>

                        {{-- Baris 2: Username & Usage --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                            <h6 class="mb-0 fw-bold text-monospace">
                                <i class="fas fa-user text-primary me-1"></i> {{ $session->username }}
                            </h6>
                            <span class="badge bg-light text-dark border px-2 py-1 text-primary fw-bold">
                                {{ $totalMb }} MB
                            </span>
                        </div>

                        {{-- Baris 3: NAS IP & Uptime --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted text-monospace">
                                <i class="fas fa-server me-1"></i> {{ $session->nasipaddress }}
                            </small>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i> {{ $uptimeFormatted }}
                            </small>
                        </div>

                        <hr class="my-2" />

                        {{-- Baris 4: Disconnect Button --}}
                        <div class="d-flex justify-content-end align-items-center">
                            <form id="mobileDisconnectForm-{{ $loop->iteration }}" method="POST"
                                action="{{ route('radius.customers.disconnect', $session->username) }}">
                                @csrf
                                <button type="button" class="btn btn-xs btn-danger px-3 py-1"
                                    onclick="confirmDisconnect('{{ $session->username }}', 'mobileDisconnectForm-{{ $loop->iteration }}')">
                                    <i class="fas fa-power-off me-1"></i> {{ __('radius.action.kick') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-info-circle me-1"></i> {{ __('radius.active_sessions.no_active_sessions') }}
                </div>
            @endforelse
        </div>
    </div>

    @section('custom_scripts')
        <script>
            function confirmDisconnect(username, formId) {
                Swal.fire({
                    title: "{{ __('radius.active_sessions.kick_title') ?? 'Putuskan Koneksi?' }}",
                    text: "{{ __('radius.active_sessions.kick_confirm') ?? 'Apakah Anda yakin ingin memutuskan session untuk user ' }}" +
                        username + "?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: "{{ __('radius.button.yes_kick') ?? 'Ya, Putuskan!' }}",
                    cancelButtonText: "{{ __('radius.button.cancel') ?? 'Batal' }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: "{{ __('radius.index.sync_processing_title') ?? 'Memproses...' }}",
                            text: "{{ __('radius.active_sessions.kick_processing_text') ?? 'Mengirim paket Packet of Disconnect (PoD)...' }}",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        document.getElementById(formId).submit();
                    }
                });
            }
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
