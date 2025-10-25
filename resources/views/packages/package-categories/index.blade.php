<x-layouts.app-layout title="Packages">

    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary">{{ __('package_categories.header.index') }} -
            {{ count($package_categories) }}
            Categories
        </h5>
        @can('package-category-create')
            <span><a class="btn btn-sm btn-success mt-2" href="{{ route('categories.create') }}"><i class="fa fa-plus"></i>
                    {{ __('package_categories.button.create') }}</a></span>
        @endcan
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="packagesTable" cellspacing="0" width="100%"
                class="table datatable-loading no-footer sortable searchable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('package_categories.table.name') }}</th>
                        <th>{{ __('package_categories.table.status') }}</th>
                        <th>{{ __('package_categories.table.description') }}</th>
                        <th class="text-end">{{ __('package_categories.table.action') }}</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach ($package_categories as $package_category)
                        <tr class="@if ($package_category->is_active == false) text-secondary @else text-primary @endif">
                            <td><span class="badge rounded-pill">{{ ++$i }}</span></td>
                            <td><span class="badge"><i class="fas fa-chalkboard"></i>
                                    {{ $package_category->name }}</span></td>
                            <td>
                                <span class="badge bg-{{ $package_category->is_active ? 'success' : 'danger' }}-soft">
                                    {{ $package_category->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td><span
                                    class="badge bg-blue-soft">{{ $package_category->description ?? 'No Description' }}</span>
                            </td>
                            <td class="d-flex items-center justify-content-end">
                                <x-drop-down-action>
                                    @can('package-category-list')
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('categories.show', $package_category->slug) }}">
                                                <i class="far fa-eye me-2 text-success"></i>
                                                {{ __('package_categories.action.show') }}
                                            </a>
                                        </li>
                                    @endcan
                                    @can('package-category-edit')
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('categories.edit', $package_category->slug) }}">
                                                <i class="far fa-edit me-2 text-success"></i>
                                                {{ __('package_categories.action.edit') }}
                                            </a>
                                        </li>
                                        {{-- <pre>{{ dd($package_category) }}</pre> --}}
                                        <li>
                                            @if ($package_category->is_active == true)
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('deactivate.package.categories', $package_category->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-times text-warning me-2"></i>
                                                        {{ __('package_categories.action.disable') }}
                                                    </a>
                                                </form>
                                            @else
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('activate.package.categories', $package_category->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check me-2 text-success"></i>
                                                        {{ __('package_categories.action.enable') }}
                                                    </a>
                                                </form>
                                            @endif
                                        </li>
                                    @endcan
                                    @can('package-category-delete')
                                        <li>
                                            <a class="dropdown-item" href="#"
                                                onclick="confirmDelete(event, {{ $package_category->id }})">
                                                <i class="fa fa-trash me-2 text-danger"></i>
                                                {{ __('package_categories.action.delete') }}
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
        {{ $package_categories->links() }}
    </div>
    @section('custom_scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            async function confirmDelete(event, id) {
                event.preventDefault();

                // Konfirmasi awal
                const {
                    isConfirmed
                } = await Swal.fire({
                    title: 'Are you sure?',
                    text: "This category data will be disabled (soft delete)",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Disable!',
                    cancelButtonText: 'Cancel'
                });

                if (!isConfirmed) {
                    await Swal.fire('Cancelled', 'Action cancelled', 'info');
                    return;
                }

                // Tampilkan loader
                let timerInterval;
                Swal.fire({
                    title: 'Processing...',
                    html: 'Currently disabling categories',
                    timerProgressBar: true,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {
                        clearInterval(timerInterval);
                    }
                });

                try {
                    // Eksekusi request
                    const response = await fetch(`/cpanel/package-categories/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    });

                    const data = await response.json();

                    // Tutup loader
                    Swal.close();

                    if (!response.ok) {
                        throw new Error(data.message || 'There is an error');
                    }

                    // Tampilkan hasil sukses
                    await Swal.fire({
                        title: 'Succeed!',
                        text: data.message || 'Category successfully disabled',
                        icon: 'success',
                        confirmButtonText: 'OK'
                    });

                    window.location.reload();

                } catch (error) {
                    // Tutup loader dan tampilkan error
                    Swal.close();

                    await Swal.fire({
                        title: 'Fail!',
                        text: error.message || 'An error occurred while processing',
                        icon: 'error',
                        confirmButtonText: 'Understand'
                    });
                }
            }
        </script>
    @endsection
</x-layouts.app-layout>
