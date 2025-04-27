<x-layouts.app-layout title="PPP Profiles">
    <div class="card-header py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
            <h5 class="m-0 font-weight-bold text-primary mb-3 mb-md-0">
                {{ __('paket.profile.header.index') }}
            </h5>
            <div class="d-flex flex-wrap" style="gap: 0.75rem !important;">
                <div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="mikrotikDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-server"></i>
                            <span class="d-none d-md-inline">{{ __('paket.profile.label.select_mikrotik') }}</span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="mikrotikDropdown">
                            @foreach ($mikrotiks as $mikrotik)
                                <li>
                                    <a class="dropdown-item" href="#" data-mikrotik-slug="{{ $mikrotik->slug }}">
                                        {{ $mikrotik->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <button id="exportProfileBtn" class="btn btn-sm btn-success" disabled>
                    <i class="fa fa-upload"></i>
                    <span class="d-none d-md-inline">{{ __('paket.profile.button.export_all') }}</span>
                </button>

                <a class="btn btn-sm btn-info" href="{{ route('ppp-profiles.import.data') }}">
                    <i class="fa fa-download"></i>
                    <span class="d-none d-md-inline">{{ __('paket.profile.button.import') }}</span>
                </a>

                <a class="btn btn-sm btn-success" href="{{ route('ppp-profiles.create') }}">
                    <i class="fa fa-plus"></i>
                    <span class="d-none d-md-inline">{{ __('paket.profile.button.create') }}</span>
                </a>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="paketsTable" class="table datatable-loading no-footer sortable searchable"
                    style="min-width: 800px">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('paket.profile.table.name') }}</th>
                            <th>{{ __('paket.profile.table.max_upload') }}</th>
                            <th>{{ __('paket.profile.table.max_download') }}</th>
                            <th>{{ __('paket.profile.table.rate_limit') }}</th>
                            <th>{{ __('paket.profile.table.use_encryption') }}</th>
                            <th>{{ __('paket.profile.table.only_one') }}</th>
                            <th>{{ __('paket.profile.table.comment') }}</th>
                            <th>{{ __('paket.profile.table.status') }}</th>
                            <th class="text-end">{{ __('paket.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($profiles as $profile)
                            @php
                                $isDisabled = $profile->disabled === 'true';
                            @endphp
                            <tr>
                                <td><span class="badge rounded-pill text-secondary">{{ ++$i }}</span></td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->profile_name }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->max_upload }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->max_download }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->rate_limit }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->use_encryption }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->only_one }}</span>
                                </td>
                                <td><span
                                        class="badge {{ $isDisabled ? 'text-secondary' : 'text-primary' }}">{{ $profile->comment }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $isDisabled ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                        <i class="fa {{ $isDisabled ? 'fa-lock' : 'fa-unlock' }}"></i>
                                        {{ $isDisabled ? 'Disabled' : 'Enabled' }}
                                    </span>
                                </td>
                                {{-- <td>
                                    <span class="badge text-primary">
                                        @if ($profile->rasio_cir)
                                            1 : {{ $profile->rasio_cir }}
                                        @endif
                                    </span>
                                </td> --}}
                                {{-- <td><span class="badge text-primary">{{ $profile->priority }}</span></td> --}}
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-secondary dropdown-toggle" type="button"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            @can('packet-list')
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('ppp-profiles.show', $profile->id) }}">
                                                        <i class="far fa-eye me-2 text-success"></i>
                                                        {{ __('paket.profile.action.show') }}
                                                    </a>
                                                </li>
                                            @endcan
                                            @can('packet-edit')
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('ppp-profiles.edit', $profile->id) }}">
                                                        <i class="far fa-edit me-2 text-success"></i>
                                                        {{ __('paket.profile.action.edit') }}
                                                    </a>
                                                </li>
                                                <li>
                                                    @if ($profile->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('ppp-profiles.disable', $profile->id) }}">
                                                            @csrf
                                                            @method('put')
                                                            <a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-times text-warning me-2"></i>
                                                                {{ __('paket.profile.action.disable') }}
                                                            </a>
                                                        </form>
                                                    @else
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('ppp-profiles.enable', $profile->id) }}">
                                                            @csrf
                                                            @method('put')
                                                            <a class="dropdown-item" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-check me-2 text-success"></i>
                                                                {{ __('paket.profile.action.enable') }}
                                                            </a>
                                                        </form>
                                                    @endif
                                                </li>
                                            @endcan
                                            @can('packet-delete')
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ route('ppp-profiles.delete', $profile->id) }}">
                                                        <i class="fa fa-trash me-2 text-danger"></i>
                                                        {{ __('paket.profile.action.delete') }}
                                                    </a>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        {{ $profiles->links() }}
    </div>

    <style>
        /* Custom responsive styles */
        @media (max-width: 768px) {
            .card-header .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.875rem;
            }

            .dropdown-menu {
                position: absolute !important;
            }

            .table-responsive {
                border: 0;
            }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedMikrotikSlug = null;

            // Handle dropdown selection
            document.querySelectorAll('.dropdown-item[data-mikrotik-slug]').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    selectedMikrotikSlug = this.getAttribute('data-mikrotik-slug');
                    const mikrotikName = this.textContent;

                    // Update dropdown button text
                    const dropdownBtn = document.getElementById('mikrotikDropdown');
                    dropdownBtn.innerHTML = `<i class="fa fa-server"></i> ${mikrotikName}`;

                    // Enable export button
                    document.getElementById('exportProfileBtn').disabled = false;
                });
            });

            // Handle export button click
            document.getElementById('exportProfileBtn').addEventListener('click', function() {
                if (selectedMikrotikSlug) {
                    Swal.fire({
                        title: 'Export Confirmation',
                        text: 'Are you sure you want to export all profiles to the selected Mikrotik?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, export!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading indicator immediately
                            Swal.fire({
                                title: 'Exporting...',
                                html: 'Please wait while profiles are being exported.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Use fetch API instead of form submission
                            fetch(`/cpanel/packets/ppp-profiles/export/${encodeURIComponent(selectedMikrotikSlug)}`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content,
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({})
                                })
                                .then(response => {
                                    if (!response.ok) {
                                        return response.json().then(err => {
                                            throw err;
                                        });
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    Swal.fire({
                                        title: 'Success!',
                                        text: data.message ||
                                            'Export completed successfully',
                                        icon: 'success'
                                    });
                                })
                                .catch(error => {
                                    const errorMsg = error.message ||
                                        (error.error && error.error.message) ||
                                        'Failed to export profiles';

                                    Swal.fire({
                                        title: 'Error!',
                                        text: errorMsg,
                                        icon: 'error'
                                    });
                                    console.error('Error:', error);
                                });
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error',
                        text: 'Please select a Mikrotik device first',
                        icon: 'error',
                        confirmButtonColor: '#3085d6'
                    });
                }
            });
        });
    </script>
</x-layouts.app-layout>
