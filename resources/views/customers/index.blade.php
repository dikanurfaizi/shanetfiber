<x-layouts.cpanel.customers.app-layout title="Customers">
    <div class="card-header py-3">
        <h5 class="m-0 fw-bold text-primary mb-2">
            {{ __('customer.header.index') }}
        </h5>

        <div>
            @if (isset($message))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ $message }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @else
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ $data->total() }} {{ __('customer.label.total-customers') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
        </div>

        <div>
            <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                data-bs-target="#collapseSearchCustomer" aria-expanded="false" aria-controls="collapseSearchCustomer">
                <i class="fas fa-search me-1"></i>
                <span>{{ __('customer.button.search') }}</span>
            </a>
            <div class="collapse mt-2" id="collapseSearchCustomer">
                <div class="card card-body p-3 bg-light rounded border">
                    <form method="GET">
                        <div class="row g-2">
                            <!-- Kolom Pencarian Nama -->
                            <div class="col-md-3 col-12">
                                <input type="search" class="form-control"
                                    placeholder="{{ __('customer.placeholder.name') }}" name="search_name"
                                    value="{{ request('search_name') }}">
                            </div>
                            <!-- Kolom Alamat -->
                            <div class="col-md-3 col-12">
                                <select name="search_address" id="select2-address-lokal" class="form-select">
                                    <option value="">{{ __('billing.placeholder.search-address') }}</option>
                                    <option value="NULL" {{ request('search_address') === 'NULL' ? 'selected' : '' }}>
                                        {{ __('billing.placeholder.search-without-address') }}
                                    </option>
                                    @foreach ($address as $addrr)
                                        <option value="{{ $addrr }}"
                                            {{ request('search_address') === $addrr ? 'selected' : '' }}>
                                            {{ $addrr }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Kolom Status -->
                            <div class="col-md-2 col-12">
                                <select name="status" class="form-select">
                                    <option value="">{{ __('customer.placeholder.all-state') }}</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.enable') }}</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.disable') }}</option>
                                    <option value="isolir" {{ request('status') === 'isolir' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.isolir') }}</option>
                                    <option value="offline" {{ request('status') === 'offline' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.offline') }}</option>
                                    <option value="online" {{ request('status') === 'online' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.online') }}</option>
                                    <option value="free" {{ request('status') === 'free' ? 'selected' : '' }}>
                                        {{ __('customer.placeholder.free') }}</option>
                                </select>
                            </div>
                            <!-- Kolom Admin -->
                            @canany(['view-customers-widget-total'])
                                <div class="col-md-2 col-12">
                                    {!! Form::select('search_admin', ['NULL' => 'Not Linked User'] + $allAdmin->toArray(), request('search_admin'), [
                                        'placeholder' => __('customer.placeholder.all-responsibility') ?? 'All Admin',
                                        'class' => 'form-select',
                                        'id' => 'admin-dropdown',
                                    ]) !!}
                                </div>
                            @endcanany
                            <!-- Kolom Server -->
                            <div class="col-md-2 col-12">
                                {!! Form::select('server_search', $allServers, request('server_search'), [
                                    'placeholder' => __('customer.placeholder.all-server') ?? 'All Server',
                                    'class' => 'form-select',
                                    'id' => 'server-dropdown',
                                ]) !!}
                            </div>
                            <!-- Kolom Package -->
                            <div class="col-md-2 col-12">
                                <select name="search_package" id="package-dropdown" class="form-select d-none">
                                    <option value="">{{ __('customer.placeholder.all-package') }}</option>
                                </select>
                            </div>
                        </div>
                        <!-- Tombol Pencarian -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-success" type="submit">
                                    <i class="fas fa-search me-1"></i>{{ __('customer.button.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
            <!-- Pagination -->
            <div>
                {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
            </div>

            <!-- Dropdown dan Export Button -->
            <form method="GET" id="paginationForm" class="d-flex align-items-center gap-2">
                <span class="fw-bold text-primary">{{ __('customer.label.show') }}</span>
                <select name="perPage" id="perPage" class="form-select form-select-sm w-auto text-center"
                    onchange="updatePerPage()">
                    <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                </select>

                @canany(['customer-export'])
                    <!-- Export Button -->
                    <a href="{{ route('customers.export') }}" class="btn btn-success btn-sm d-flex align-items-center">
                        <i class="fas fa-file-excel me-1"></i> {{ __('customer.button.export') }}
                    </a>
                @endcanany
            </form>
        </div>

        <div class="d-none d-md-block">
            <div class="table-responsive">
                <table id="customersTable" class="table table-bordered table-striped table-hover align-middle w-100">
                    <tr class="table-secondary text-center">
                        <th><span class="badge text-primary">{{ __('customer.table.no') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.name') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.contact') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.address') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.activation_date') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.package') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.bill') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.responsibility') }}</span></th>
                        <th><span class="badge text-primary">{{ __('customer.table.state') }}</span></th>
                        @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                            <th width="80px"><span class="badge text-primary">{{ __('customer.table.action') }}</span>
                            </th>
                        @endcanany
                    </tr>
                    @foreach ($data as $key => $customer)
                        <tr class="{{ !$customer->is_active ? 'text-secondary' : 'text-primary' }}">
                            <td class="text-center">
                                <span class="badge text-primary">{{ ++$i }}</span>
                            </td>
                            <td>
                                @can('customer-list')
                                    <a class="{{ !$customer->is_active ? 'text-secondary' : 'text-primary' }}"
                                        href="{{ route('customers.show', $customer->slug) }}">{{ $customer->name }}</a>
                                @else
                                    {{ $customer->name }}
                                @endcan
                            </td>
                            <td><span class="badge text-primary">{{ $customer->contact }}</span></td>
                            <td><span class="badge text-primary">{{ $customer->address }}</span></td>
                            <td>
                                <span class="badge text-primary">
                                    @if ($customer->activation)
                                        {{ Carbon\Carbon::parse($customer->activation_date)->translatedFormat('d F Y') ?? 'No Data' }}
                                    @else
                                        <i class="text-danger">{{ __('Not Activated') }}</i>
                                    @endif
                                </span>
                            </td>
                            <td>
                                <span class="badge text-primary">
                                    <i class="fas fa-chalkboard me-1"></i>
                                    {{ $customer->active_package->name ?? 'No Active Package' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge text-primary">
                                    @moneyIDR(optional($customer->active_package)->price - optional($customer->member)->discount_value)
                                </span>
                            </td>
                            <td>
                                <span class="badge text-primary">
                                    <i class="fas fa-user-shield me-1"></i>
                                    {{ $customer->user?->name ?? 'No Linked User' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if (!$customer->activation)
                                    <span class="badge bg-danger">
                                        <i class="fa fa-times me-1"></i>
                                        {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if ($customer->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-lock-open me-1"></i>
                                            {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-lock me-1"></i>
                                            {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                                <td class="text-end">
                                    <x-drop-down-action>
                                        @can('customer-edit')
                                            <li>
                                                <a class="dropdown-item py-2"
                                                    href="{{ route('customers.edit', $customer->slug) }}">
                                                    <i
                                                        class="far fa-edit me-2 text-primary"></i>{{ __('customer.action.edit') }}
                                                </a>
                                            </li>
                                        @endcan

                                        @can('customer-edit')
                                            @if (!$customer->activation)
                                                <li>
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.activation', $customer->slug) }}">
                                                        @csrf @method('PUT')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i
                                                                class="fa fa-check me-2 text-success"></i>{{ __('customer.action.activation') }}
                                                        </a>
                                                    </form>
                                                </li>
                                            @else
                                                @if (!$customer->is_active)
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.enable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-check me-2 text-success"></i>{{ __('pppoe.secret.enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceEnable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-check me-2 text-success"></i>{{ __('pppoe.secret.force-enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.disable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-times me-2 text-warning"></i>{{ __('pppoe.secret.disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceDisable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-times me-2 text-warning"></i>{{ __('pppoe.secret.force-disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endif
                                        @endcan

                                        <li>
                                            <a class="dropdown-item py-2"
                                                href="{{ route('customers.resend-activation', $customer->slug) }}">
                                                <i
                                                    class="fas fa-paper-plane me-2 text-info"></i>{{ __('customer.action.resend-activation') }}
                                            </a>
                                        </li>

                                        @can('secret-edit')
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('customers.pppoe.export', $customer->slug) }}">
                                                    @csrf @method('PUT')
                                                    <a class="dropdown-item py-2" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i
                                                            class="fas fa-file-export me-2 text-secondary"></i>{{ __('pppoe.secret.export') }}
                                                    </a>
                                                </form>
                                            </li>
                                        @endcan

                                        @can('customer-delete')
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger fw-bold"
                                                    href="{{ route('customers.delete', $customer->slug) }}">
                                                    <i
                                                        class="fa fa-ban me-2 text-danger"></i>{{ __('customer.action.suspend') }}
                                                </a>
                                            </li>
                                        @endcan
                                    </x-drop-down-action>
                                </td>
                            @endcanany
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="mt-3">
                {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
            </div>
        </div>

        <!-- Mobile View -->
        <div class="d-md-none">
            @foreach ($data as $key => $customer)
                <div class="card mb-3 shadow-sm border text-start">
                    <div class="card-body p-2">
                        {{-- Baris 1: #No dan Status --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="badge rounded-pill bg-secondary px-2 py-1">
                                #{{ $data->firstItem() + $loop->index }}
                            </span>
                            <div>
                                @if (!$customer->activation)
                                    <span class="badge text-white bg-danger py-1">
                                        <i class="fa fa-times me-1"></i> {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if ($customer->is_active)
                                        <span class="badge text-white bg-success py-1">
                                            <i class="fas fa-lock-open me-1"></i> {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @else
                                        <span class="badge text-white bg-secondary py-1">
                                            <i class="fas fa-lock me-1"></i> {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Baris 2: Nama Pelanggan + Nama Paket --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                            <h6 class="mb-0 fw-bold" style="max-width: 60%">
                                @can('customer-list')
                                    <a class="{{ !$customer->is_active ? 'text-secondary' : 'text-primary' }}"
                                        href="{{ route('customers.show', $customer->slug) }}">
                                        {{ $customer->name }}
                                    </a>
                                @else
                                    {{ $customer->name }}
                                @endcan
                            </h6>
                            <span class="badge bg-light text-dark border text-truncate px-2 py-1 text-primary fw-bold"
                                style="max-width: 45%;"
                                title="{{ $customer->active_package->name ?? 'No Active Package' }}">
                                {{ $customer->active_package->name ?? 'No Active Package' }}
                            </span>
                        </div>

                        {{-- Baris 3: Nomor Telepon & Harga Paket --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted text-truncate" style="max-width: 60%;">
                                <i class="fas fa-phone-alt fa-xs me-1"></i> {{ $customer->contact }}
                            </small>
                            <small class="fw-bold text-dark">
                                @php
                                    $harga =
                                        optional($customer->active_package)->price -
                                        optional($customer->member)->discount_value;
                                @endphp
                                @moneyIDR($harga)
                            </small>
                        </div>

                        {{-- Baris 4: Alamat Lengkap --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted text-truncate" style="max-width: 100%;">
                                <i class="fas fa-map-marker-alt fa-xs me-1"></i> {{ $customer->address }}
                            </small>
                        </div>

                        <hr class="my-2" />

                        {{-- Baris 5: Penanggung Jawab Admin + Dropdown Action --}}
                        <div
                            class="d-flex justify-content-between align-items-center bg-light px-2 py-1 rounded border">
                            <small class="text-muted fw-semibold">
                                <i class="fas fa-user-shield me-1"></i> {{ $customer->user?->name ?? 'No Admin' }}
                            </small>

                            @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-primary dropdown-toggle px-2" type="button"
                                        id="mobileDropdown-{{ $customer->slug }}" data-bs-toggle="dropdown"
                                        aria-expanded="false">
                                        <i class="fas fa-cog"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow p-1"
                                        aria-labelledby="mobileDropdown-{{ $customer->slug }}">
                                        @can('customer-edit')
                                            <li>
                                                <a class="dropdown-item py-2"
                                                    href="{{ route('customers.edit', $customer->slug) }}">
                                                    <i
                                                        class="far fa-edit me-2 text-primary"></i>{{ __('customer.action.edit') }}
                                                </a>
                                            </li>
                                        @endcan

                                        @can('customer-edit')
                                            @if (!$customer->activation)
                                                <li>
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.activation', $customer->slug) }}">
                                                        @csrf @method('PUT')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i
                                                                class="fa fa-check me-2 text-success"></i>{{ __('customer.action.activation') }}
                                                        </a>
                                                    </form>
                                                </li>
                                            @else
                                                @if (!$customer->is_active)
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.enable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-check me-2 text-success"></i>{{ __('pppoe.secret.enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceEnable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-check me-2 text-success"></i>{{ __('pppoe.secret.force-enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @else
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.disable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-times me-2 text-warning"></i>{{ __('pppoe.secret.disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceDisable', $customer->slug) }}">
                                                            @csrf @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i
                                                                    class="fa fa-times me-2 text-warning"></i>{{ __('pppoe.secret.force-disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endif
                                        @endcan

                                        <li>
                                            <a class="dropdown-item py-2"
                                                href="{{ route('customers.resend-activation', $customer->slug) }}">
                                                <i
                                                    class="fas fa-paper-plane me-2 text-info"></i>{{ __('customer.action.resend-activation') }}
                                            </a>
                                        </li>

                                        @can('secret-edit')
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('customers.pppoe.export', $customer->slug) }}">
                                                    @csrf @method('PUT')
                                                    <a class="dropdown-item py-2" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i
                                                            class="fas fa-file-export me-2 text-secondary"></i>{{ __('pppoe.secret.export') }}
                                                    </a>
                                                </form>
                                            </li>
                                        @endcan

                                        @can('customer-delete')
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-2 text-danger fw-bold"
                                                    href="{{ route('customers.delete', $customer->slug) }}">
                                                    <i
                                                        class="fa fa-ban me-2 text-danger"></i>{{ __('customer.action.suspend') }}
                                                </a>
                                            </li>
                                        @endcan
                                    </ul>
                                </div>
                            @endcanany
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
                <div>
                    {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <script>
            $(document).ready(function() {
                $('#select2-address-lokal').select2({
                    theme: 'bootstrap-5',
                    placeholder: "{{ __('billing.placeholder.search-address') }}",
                    allowClear: true,
                    width: '100%'
                });
            });

            function updatePerPage() {
                let perPage = document.getElementById('perPage').value;
                let url = new URL(window.location.href);
                url.searchParams.set('perPage', perPage);
                window.location.href = url.toString();
            }
        </script>
    @endsection

    @section('custom_styles')
        <style>
            .btn-disabled {
                opacity: 0.3 !important;
                pointer-events: none !important;
            }

            .bg-turquoise-soft {
                background-color: #e0f7f4;
            }

            .text-turquoise {
                color: #009688;
            }

            .bg-gray-soft {
                background-color: lightgray !important;
            }
        </style>
    @endsection

</x-layouts.cpanel.customers.app-layout>
