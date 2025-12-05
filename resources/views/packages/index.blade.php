<x-layouts.app-layout title="Packages">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('package.header.index') }} - {{ count($packages) }} Packages
        </h5>
        @can('package-create')
            <span><a class="btn btn-sm btn-success mt-2" href="{{ route('packages.create') }}"><i class="fa fa-plus"></i>
                    {{ __('package.button.create') }}</a></span>
        @endcan
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('package.table.name') }}</th>
                        <th>{{ __('package.table.price') }}</th>
                        <th>{{ __('package.table.profile') }}</th>
                        <th>{{ __('package.table.status') }}</th>
                        <th>{{ __('package.table.description') }}</th>
                        @can('package-edit')
                            <th>{{ __('package.table.display') }}</th>
                        @endcan
                        <th class="text-end">{{ __('package.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($packages as $package)
                        <tr class="@if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i> {{ $package->name }}</span></td>
                            <td><span
                                    class="badge @if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                                    @if ($package->price === 0)
                                        {{ __('Free') }}
                                    @else
                                        @moneyIDR($package->price)
                                    @endif
                                </span></td>
                            <td>
                                <span
                                    class="badge @if ($package->disabled === 'true') text-secondary @else text-primary @endif">
                                    {{ $package->profile }}</span>
                            </td>
                            <td>
                                @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span class="badge text-white bg-success">
                                        <i class="fas fa-lock-open"></i> {{ __('package.info.enabled') }}
                                    </span>
                                @endif
                                @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span class="badge text-white bg-secondary">
                                        <i class="fas fa-lock"></i> {{ __('package.info.disabled') }}
                                    </span>
                                @endif

                            </td>
                            <td><span class="badge bg-blue-soft">{{ $package->description ?? 'No Description' }}</span>
                            </td>
                            @can('package-edit')
                                <td>
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input toggle-display" type="checkbox" role="switch"
                                            data-id="{{ $package->id }}"
                                            {{ $package->display_to_customer ? 'checked' : '' }}
                                            data-slug="{{ $package->slug }}">
                                    </div>
                                </td>
                            @endcan

                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>
                                    @can('package-list')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packages.show', $package->slug) }}">
                                                <i class="far fa-eye me-2 text-success"></i>
                                                {{ __('package.action.show') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('package-edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('packages.edit', $package->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('package.action.edit') }}
                                            </a>
                                        </li>
                                        <li>
                                            @if ($package->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('disable.package', $package->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-times text-warning me-2"></i>
                                                        {{ __('package.profile.action.disable') }}
                                                    </a>
                                                </form>
                                            @else
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('enable.package', $package->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check me-2 text-success"></i>
                                                        {{ __('package.profile.action.enable') }}
                                                    </a>
                                                </form>
                                            @endif

                                        </li>
                                    @endcan
                                    @can('package-delete')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('package.delete', $package->slug) }}">
                                                <i class="fa fa-trash me-2 text-danger"></i>
                                                {{ __('package.action.delete') }}
                                            </a>
                                        </li>
                                    @endcan
                                </x-drop-down-action>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $packages->links() }}
    </div>

    @can('package-edit')
        @section('custom_scripts')
            <script>
                const toggleDisplayRoute = "{{ route('package.toggle-display', ':slug') }}";
            </script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('.toggle-display').forEach(toggle => {

                        toggle.addEventListener('change', function(event) {
                            const checkbox = event.target;
                            const packageSlug = checkbox.dataset.slug;
                            const isChecked = checkbox.checked ? 1 : 0;

                            const url = toggleDisplayRoute.replace(':slug', packageSlug);

                            fetch(url, {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector(
                                            'meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        display_to_customer: isChecked
                                    })
                                })
                                .then(res => res.json())
                                .then(response => {

                                    if (response.status !== 'success') {

                                        new Notify({
                                            status: response.status || 'error',
                                            title: response.title || 'Update Failed',
                                            text: response.message ||
                                                'Gagal memperbarui status!',
                                            effect: 'slide',
                                            speed: 500,
                                            showCloseButton: true,
                                            autotimeout: 5000,
                                            autoclose: true
                                        });

                                        checkbox.checked = !isChecked; // rollback
                                        return;
                                    }

                                    new Notify({
                                        status: response.status,
                                        title: response.title,
                                        text: response.message,
                                        effect: 'slide',
                                        speed: 500,
                                        showCloseButton: true,
                                        autotimeout: 5000,
                                        autoclose: true
                                    });

                                })
                                .catch(() => {
                                    new Notify({
                                        status: 'error',
                                        title: 'Connection Error',
                                        text: 'Terjadi kesalahan koneksi!',
                                        effect: 'slide',
                                        speed: 500,
                                        showCloseButton: true,
                                        autotimeout: 5000,
                                        autoclose: true
                                    });

                                    checkbox.checked = !isChecked;
                                });

                        });

                    });
                });
            </script>
        @endsection
    @endcan
</x-layouts.app-layout>
