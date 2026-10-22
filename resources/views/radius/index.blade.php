<x-layouts.cpanel.settings.app-layout :title="__('radius.index.title')">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary mb-2">
            {{ __('radius.index.header') }}
        </h5>

        <span>
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ $errors->first() }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif
        </span>

        {{-- Form Filter & Action Header --}}
        <span>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchRadius"
                aria-expanded="false" aria-controls="collapseSearchRadius" style="padding-top: inherit;">
                <i class="fas fa-search"></i>
                <span>{{ __('radius.index.search_toggle') }}</span>
            </a>
            <div class="collapse" id="collapseSearchRadius">
                <div class="card-header py-3 rounded">
                    <form method="GET" action="{{ route('radius.index') }}">
                        <div class="row">
                            <div class="col-md-4 col-12 mb-2">
                                <input type="search" class="form-control"
                                    placeholder="{{ __('radius.index.search_placeholder') }}" name="search"
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-3 col-12 mb-2">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('radius.index.all_status') }}</option>
                                    <option value="synced" {{ request('status') === 'synced' ? 'selected' : '' }}>
                                        {{ __('radius.status.radius_active') }}
                                    </option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>
                                        {{ __('radius.status.error_sync') }}
                                    </option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>
                                        {{ __('radius.status.pending') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-3 col-12 mb-2">
                                <button class="btn btn-success btn-sm h-100" type="submit">
                                    <i class="fas fa-search mr-1"></i> {{ __('radius.index.btn_search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </span>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <div>
                {{ $customers->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>

            <div class="d-flex align-items-center gap-2">
                {{-- Tombol Refresh Data DB Terakhir --}}
                <a href="{{ route('radius.index', request()->query()) }}"
                    class="btn btn-secondary btn-sm d-flex align-items-center mr-2" style="height: 35px;"
                    title="{{ __('radius.index.btn_refresh_title') }}">
                    <i class="fas fa-sync-alt mr-1"></i> {{ __('radius.index.btn_refresh') }}
                </a>

                {{-- Tombol Export Active Users Massal ke FreeRADIUS --}}
                <form id="bulkSyncForm" method="POST" action="{{ route('radius.export-active') }}" class="d-inline">
                    @csrf
                    <button type="button" class="btn btn-primary btn-sm d-flex align-items-center"
                        style="height: 35px;" onclick="confirmBulkSync()">
                        <i class="fas fa-database mr-2"></i> {{ __('radius.index.btn_bulk_sync') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Desktop View Table --}}
        <div class="d-none d-md-block">
            <div style="overflow-x: auto;">
                <table id="radiusTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr class="table-secondary text-center">
                            <th><span class="badge bg-danger-soft text-primary">{{ __('radius.table.no') }}</span></th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.customer_name') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.pppoe_username') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.package_profile') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.radius_status') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.mikrotik_status') }}</span>
                            </th>
                            <th><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.last_sync') }}</span>
                            </th>
                            <th width="100px"><span
                                    class="badge bg-danger-soft text-primary">{{ __('radius.table.action') }}</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $key => $customer)
                            @php
                                $state = $customer->provisioningState;
                                $username = $customer->username ?? $customer->contact;
                                $packageName =
                                    $customer->activeSubscription->package->name ?? __('radius.index.no_package');
                                $mikrotikStatus = $state->mikrotik_status ?? 'synced';
                            @endphp
                            <tr class="text-center align-middle">
                                <td><span
                                        class="badge bg-danger-soft">{{ $customers->firstItem() + $loop->index }}</span>
                                </td>
                                <td class="text-left font-weight-bold">
                                    <a href="{{ route('customers.show', $customer->slug) }}">{{ $customer->name }}</a>
                                </td>
                                <td><span class="badge bg-light text-dark font-monospace">{{ $username }}</span>
                                </td>
                                <td><span class="badge bg-turquoise-soft text-turquoise">{{ $packageName }}</span>
                                </td>

                                {{-- Status FreeRADIUS --}}
                                <td>
                                    @if ($state && $state->radius_status === 'synced')
                                        <span class="badge bg-success text-white"
                                            title="{{ __('radius.status.active_in_radius') }}">
                                            <i class="fas fa-check-circle mr-1"></i> {{ __('radius.status.synced') }}
                                        </span>
                                    @elseif ($state && $state->radius_status === 'failed')
                                        <span class="badge bg-danger text-white" data-toggle="tooltip"
                                            title="{{ $state->last_error_message }}">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            {{ __('radius.status.failed') }}
                                        </span>
                                    @else
                                        <span class="badge bg-warning text-dark"
                                            title="{{ __('radius.status.not_synced_yet') }}">
                                            <i class="fas fa-clock mr-1"></i> {{ __('radius.status.pending') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Status MikroTik API Native --}}
                                <td>
                                    @if ($mikrotikStatus === 'synced')
                                        <span class="badge bg-info text-white"
                                            title="{{ __('radius.status.connected_via_api') }}">
                                            <i class="fas fa-network-wired mr-1"></i>
                                            {{ __('radius.status.connected') }}
                                        </span>
                                    @elseif ($mikrotikStatus === 'failed')
                                        <span class="badge bg-danger text-white"
                                            title="{{ __('radius.status.mikrotik_error') }}">
                                            <i class="fas fa-times-circle mr-1"></i> {{ __('radius.status.error') }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary text-white">
                                            <i class="fas fa-minus-circle mr-1"></i>
                                            {{ __('radius.status.unsynced') }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <small class="text-muted">
                                        {{ $state?->last_radius_sync_at ? $state->last_radius_sync_at->translatedFormat('d M Y H:i') : '-' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    <x-drop-down-action>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('radius.sync-single-radius', $customer->slug) }}">
                                                @csrf
                                                <a class="dropdown-item py-2" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fas fa-database text-primary mr-2"></i>
                                                    {{ __('radius.action.sync_radius') }}
                                                </a>
                                            </form>
                                        </li>
                                        <li>
                                            <form method="POST"
                                                action="{{ route('radius.sync-single-mikrotik', $customer->slug) }}">
                                                @csrf
                                                <a class="dropdown-item py-2" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fas fa-network-wired text-warning mr-2"></i>
                                                    {{ __('radius.action.sync_mikrotik') }}
                                                </a>
                                            </form>
                                        </li>
                                    </x-drop-down-action>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    {{ __('radius.index.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $customers->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>

        {{-- Mobile View Cards --}}
        <div class="d-md-none">
            @forelse ($customers as $key => $customer)
                @php
                    $state = $customer->provisioningState;
                    $username = $customer->username ?? $customer->contact;
                    $packageName = $customer->activeSubscription->package->name ?? __('radius.index.no_package');
                    $mikrotikStatus = $state->mikrotik_status ?? 'synced';
                @endphp
                <div class="card mb-3 shadow-sm border text-left">
                    <div class="card-body p-2">

                        {{-- Baris 1: No & Dual Status Badges --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="badge badge-pill badge-secondary px-2 py-1">
                                #{{ $customers->firstItem() + $loop->index }}
                            </span>
                            <div class="d-flex gap-1">
                                {{-- Badge RADIUS --}}
                                @if ($state && $state->radius_status === 'synced')
                                    <span class="badge text-white bg-success py-1 mr-1">
                                        <i class="fas fa-database mr-1"></i> RADIUS: OK
                                    </span>
                                @else
                                    <span class="badge text-dark bg-warning py-1 mr-1">
                                        <i class="fas fa-clock mr-1"></i> RADIUS: Pending
                                    </span>
                                @endif

                                {{-- Badge MikroTik --}}
                                @if ($mikrotikStatus === 'synced')
                                    <span class="badge text-white bg-info py-1">
                                        <i class="fas fa-network-wired mr-1"></i> MK: OK
                                    </span>
                                @endif
                            </div>
                        </div>

                        {{-- Baris 2: Nama & Paket --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                            <h6 class="mb-0 font-weight-bold" style="max-width: 60%">
                                <a href="{{ route('customers.show', $customer->slug) }}">{{ $customer->name }}</a>
                            </h6>
                            <span
                                class="badge badge-light border text-truncate px-2 py-1 text-primary font-weight-bold"
                                style="max-width: 45%;">
                                {{ $packageName }}
                            </span>
                        </div>

                        {{-- Baris 3: Username PPPoE & Last Sync --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted text-monospace">
                                <i class="fas fa-user mr-1"></i> {{ $username }}
                            </small>
                            <small class="text-muted">
                                {{ $state?->last_radius_sync_at ? $state->last_radius_sync_at->translatedFormat('d/m/y H:i') : '-' }}
                            </small>
                        </div>

                        <hr class="my-2" />

                        {{-- Baris 4: Actions Dropdown --}}
                        <div
                            class="d-flex justify-content-between align-items-center bg-light px-2 py-1 rounded border">
                            <small class="text-muted font-weight-600">
                                <i class="fas fa-sliders-h mr-1"></i> {{ __('radius.index.control') }}
                            </small>

                            <div class="dropdown">
                                <button class="btn btn-xs btn-outline-primary dropdown-toggle px-2" type="button"
                                    id="mobileDropdown-{{ $customer->slug }}" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-cog"></i> {{ __('radius.index.btn_action') }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in p-1">
                                    <form method="POST"
                                        action="{{ route('radius.sync-single-radius', $customer->slug) }}">
                                        @csrf
                                        <a class="dropdown-item py-2" href="#"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fas fa-database text-primary mr-2"></i>
                                            {{ __('radius.action.sync_radius') }}
                                        </a>
                                    </form>
                                    <form method="POST"
                                        action="{{ route('radius.sync-single-mikrotik', $customer->slug) }}">
                                        @csrf
                                        <a class="dropdown-item py-2" href="#"
                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="fas fa-network-wired text-warning mr-2"></i>
                                            {{ __('radius.action.sync_mikrotik') }}
                                        </a>
                                    </form>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="text-center py-4 text-muted">{{ __('radius.index.no_data') }}</div>
            @endforelse

            <div class="mt-3">
                {{ $customers->appends(request()->query())->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
            function confirmBulkSync() {
                Swal.fire({
                    title: "{{ __('radius.index.bulk_sync_title') ?? 'Konfirmasi Sync Massal' }}",
                    text: {!! json_encode(__('radius.index.bulk_sync_confirm')) !!},
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: "{{ __('radius.button.yes_export') ?? 'Ya, Jalankan!' }}",
                    cancelButtonText: "{{ __('radius.button.cancel') ?? 'Batal' }}",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tampilkan loading indicator SweetAlert agar user tahu proses sedang berjalan
                        Swal.fire({
                            title: "{{ __('radius.index.sync_processing_title') ?? 'Memproses...' }}",
                            text: "{{ __('radius.index.sync_processing_text') ?? 'Harap tunggu, proses ekspor sedang dikirim.' }}",
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        document.getElementById('bulkSyncForm').submit();
                    }
                });
            }
        </script>
    @endsection
</x-layouts.cpanel.settings.app-layout>
