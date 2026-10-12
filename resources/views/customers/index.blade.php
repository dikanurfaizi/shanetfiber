<x-layouts.cpanel.customers.app-layout title="Customers">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary mb-2">
            {{ __('customer.header.index') }}
        </h5>

        <span>
            @if (isset($message))
                <div class="alert alert-danger">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ $message }}
                </div>
            @else
                <div class="alert alert-success">
                    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                    {{ $data->total() }} {{ __('customer.label.total-customers') }}
                </div>
            @endif
        </span>
        <span>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
                aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;">
                <i class="fas fa-search"></i>
                <span>{{ __('customer.button.search') }}</span>
            </a>
            <div class="collapse" id="collapseSearchCustomer">
                <div class="card-header py-3 rounded">
                    <form>
                        <div class="row">
                            <!-- Kolom Pencarian Nama -->
                            <div class="col-md-3 col-12 mb-2">
                                <input type="search" class="form-control"
                                    placeholder="{{ __('customer.placeholder.name') }}" name="search_name">
                            </div>
                            <!-- Kolom Alamat -->
                            <div class="col-md-3 col-12 mb-2">
                                <select name="search_address" id="select2-address-lokal" class="form-control">
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
                            <div class="col-md-2 col-12 mb-2">
                                <select name="status" class="form-control">
                                    <option value="">{{ __('customer.placeholder.all-state') }}</option>
                                    <option value="false">{{ __('customer.placeholder.enable') }}</option>
                                    <option value="true">{{ __('customer.placeholder.disable') }}</option>
                                    <option value="isolir">{{ __('customer.placeholder.isolir') }}</option>
                                    <option value="offline">{{ __('customer.placeholder.offline') }}</option>
                                    <option value="online">{{ __('customer.placeholder.online') }}</option>
                                    <option value="free">{{ __('customer.placeholder.free') }}</option>
                                    <option value="deactivated">{{ __('customer.placeholder.deactivated') }}</option>
                                </select>
                            </div>
                            <!-- Kolom Admin -->
                            @canany(['view-customers-widget-total'])
                                <div class="col-md-2 col-12 mb-2">
                                    {!! Form::select('search_admin', ['NULL' => 'Not Linked User'] + $allAdmin->toArray(), null, [
                                        'placeholder' => __('customer.placeholder.all-responsibility') ?? 'All Admin',
                                        'class' => 'form-control',
                                        'id' => 'admin-dropdown',
                                    ]) !!}
                                </div>
                            @endcanany
                            <!-- Kolom Server -->
                            <div class="col-md-2 col-12 mb-2">
                                {!! Form::select('server_search', $allServers, null, [
                                    'placeholder' => __('customer.placeholder.all-server') ?? 'All Server',
                                    'class' => 'form-control',
                                    'id' => 'server-dropdown',
                                ]) !!}
                            </div>
                            <!-- Kolom Package -->
                            <div class="col-md-2 col-12 mb-2">
                                <select name="search_package" id="package-dropdown" class="form-control d-none">
                                    <option value="">{{ __('customer.placeholder.all-package') }}</option>
                                </select>
                            </div>
                        </div>
                        <!-- Tombol Pencarian -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-search"></i>
                                    {{ __('customer.button.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </span>
    </div>

    <div class="card-body">
        {{-- {{ $data->withQueryString()->links() }} --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <!-- Pagination -->
            <div>
                {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
            </div>

            <!-- Dropdown dan Export Button -->
            <form method="GET" id="paginationForm" class="d-flex align-items-center" style="margin: 5px;">
                <div class="d-flex align-items-center gap-2">
                    <span class="font-weight-bold text-primary">{{ __('customer.label.show') }}</span>
                    <select name="perPage" id="perPage" class="form-control" onchange="updatePerPage()"
                        style="width: auto; min-width: 70px; height: 35px; padding: 2px 8px; text-align: center; margin-right: 10px; margin-left: 10px;">
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                @canany(['customer-export'])
                    <!-- Export Button -->
                    <a href="{{ route('customers.export') }}" class="btn btn-success btn-sm d-flex align-items-center"
                        style="height: 35px;">
                        <i class="fas fa-file-excel" style="margin-right: 5px;"></i> {{ __('customer.button.export') }}
                    </a>
                @endcanany
            </form>
        </div>
        <div class="d-none d-md-block">
            <div style="overflow-x: auto;">
                <table id="customersTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <tr class="table-secondary text-center">
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.no') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.name') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.contact') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.address') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.activation_date') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.package') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.bill') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.responsibility') }}
                            </span>
                        </th>
                        <th>
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.state') }}
                            </span>
                        </th>
                        @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                            <th width="80px">
                                <span class="badge bg-danger-soft text-primary">
                                    {{ __('customer.table.action') }}
                                </span>
                            </th>
                        @endcanany
                    </tr>
                    @foreach ($data as $key => $customer)
                        <tr class="@if (!$customer->is_active) text-secondary @else text-primary @endif">
                            <td>
                                <span class="badge bg-danger-soft">
                                    {{ ++$i }}
                                </span>
                            </td>
                            <td>
                                @can('customer-list')
                                    <a class="@if (!$customer->is_active) text-secondary @else text-primary @endif"
                                        href="{{ route('customers.show', $customer->slug) }}">{{ $customer->name }}</a>
                                @else
                                    {{ $customer->name }}
                                @endcan
                            </td>
                            <td>
                                <span class="badge bg-danger-soft">
                                    {{ $customer->contact }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-soft">
                                    {{ $customer->address }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-soft">
                                    @if ($customer->activation)
                                        {{ Carbon\Carbon::parse($customer->activation_date)->translatedFormat('d F Y') ?? 'No Data' }}
                                    @else
                                        <i class="text-danger">{{ __('Not Activated') }}</i>
                                    @endif
                            </td>
                            </span>
                            <td>
                                <span class="badge bg-danger-soft">
                                    <i class="fas fa-chalkboard"></i>
                                    {{ $customer->active_package->name ?? 'No Active Package' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-soft">
                                    @moneyIDR(optional($customer->active_package)->price - optional($customer->member)->discount_value)
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-danger-soft">
                                    <i class="fas fa-user-shield"></i>
                                    {{ $customer->user?->name ?? 'No Linked User' }}
                                </span>
                            </td>

                            <td>
                                @if (!$customer->activation)
                                    {{-- Belum Aktivasi Awal --}}
                                    <span class="badge text-white bg-danger">
                                        <i class="fa fa-times text-warning me-2"></i>
                                        {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if ($customer->is_active)
                                        {{-- Status: Aktif / Enabled --}}
                                        <span class="badge text-white bg-success">
                                            <i class="fas fa-lock-open me-1"></i>
                                            {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @else
                                        {{-- Status: Non-Aktif / Disabled --}}
                                        <span class="badge text-white bg-danger">
                                            <i class="fas fa-lock me-1"></i>
                                            {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </td>
                            @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                                <td class="text-right"> <x-drop-down-action>

                                        @can('customer-edit')
                                            <li>
                                                <a class="dropdown-item py-2"
                                                    href="{{ route('customers.edit', $customer->slug) }}">
                                                    <i class="far fa-edit mr-2 text-primary"></i>
                                                    {{ __('customer.action.edit') }}
                                                </a>
                                            </li>
                                        @endcan

                                        @can('customer-edit')
                                            {{-- 1. Tahap Pertama: Cek Aktivasi Awal Pelanggan --}}
                                            @if (!$customer->activation)
                                                <li>
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.activation', $customer->slug) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i class="fa fa-check mr-2 text-success"></i>
                                                            {{ __('customer.action.activation') }}
                                                        </a>
                                                    </form>
                                                </li>
                                            @else
                                                {{-- 2. Tahap Kedua: Pelanggan Sudah Aktif, Cek Status Operasional (is_active) --}}
                                                @if (!$customer->is_active)
                                                    {{-- Status: Non-Aktif / Disabled --}}
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.enable', $customer->slug) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-check mr-2 text-success"></i>
                                                                {{ __('pppoe.secret.enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceEnable', $customer->slug) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-check mr-2 text-success"></i>
                                                                {{ __('pppoe.secret.force-enable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @else
                                                    {{-- Status: Aktif / Enabled --}}
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.disable', $customer->slug) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-times text-warning mr-2"></i>
                                                                {{ __('pppoe.secret.disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                    <li>
                                                        <form method="POST" class="d-inline"
                                                            action="{{ route('customers.forceDisable', $customer->slug) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <a class="dropdown-item py-2" href="#"
                                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                                <i class="fa fa-times text-warning mr-2"></i>
                                                                {{ __('pppoe.secret.force-disable') }}
                                                            </a>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endif
                                        @endcan
                                        <li>
                                            <a class="dropdown-item py-2"
                                                href="{{ route('customers.resend-activation', $customer->slug) }}">
                                                <i class="fas fa-paper-plane mr-2 text-info"></i>
                                                {{ __('customer.action.resend-activation') }}
                                            </a>
                                        </li>

                                        @can('secret-edit')
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('customers.pppoe.export', $customer->slug) }}">
                                                    @csrf @method('PUT')
                                                    <a class="dropdown-item py-2" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fas fa-file-export mr-2 text-secondary"></i>
                                                        {{ __('pppoe.secret.export') }}
                                                    </a>
                                                </form>
                                            </li>
                                        @endcan

                                        @can('customer-delete')
                                            <div class="dropdown-divider"></div>
                                            <li>
                                                <a class="dropdown-item py-2"
                                                    href="{{ route('customers.delete', $customer->slug) }}">
                                                    <i class="fa fa-ban mr-2 text-danger"></i>
                                                    {{ __('customer.action.suspend') }}
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
            {{-- {{ $data->withQueryString()->links() }} --}}
            {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
        </div>

        <!-- Mobile View -->
        <div class="d-md-none">
            @foreach ($data as $key => $customer)
                <div class="card mb-3 shadow-sm border text-left">
                    <div class="card-body p-2">

                        {{-- Baris 1: #No dan Status --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 border-bottom pb-1">
                            <span class="badge badge-pill badge-secondary px-2 py-1">
                                #{{ $data->firstItem() + $loop->index }}
                            </span>
                            <div>
                                @if (!$customer->activation)
                                    <span class="badge text-white bg-danger py-1">
                                        <i class="fa fa-times mr-1"></i> {{ __('customer.info.not-activation') }}
                                    </span>
                                @else
                                    @if (!$customer->is_active)
                                        <span class="badge text-white bg-success py-1">
                                            <i class="fas fa-lock-open mr-1"></i> {{ __('pppoe.secret.enabled') }}
                                        </span>
                                    @endif
                                    @if ($customer->is_active)
                                        <span class="badge text-white bg-secondary py-1">
                                            <i class="fas fa-lock mr-1"></i> {{ __('pppoe.secret.disabled') }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Baris 2: Nama Pelanggan + Nama Paket --}}
                        <div class="d-flex justify-content-between align-items-center mb-1 mt-2">
                            <h6 class="mb-0 font-weight-bold" style="max-width: 60%">
                                @can('customer-list')
                                    <a class="{{ !$customer->is_active ? 'text-secondary' : 'text-primary' }}"
                                        href="{{ route('customers.show', $customer->slug) }}">
                                        {{ $customer->name }}
                                    </a>
                                @else
                                    {{ $customer->name }}
                                @endcan
                            </h6>
                            <span
                                class="badge badge-light border text-truncate px-2 py-1 text-primary font-weight-bold"
                                style="max-width: 45%;"
                                title="{{ $customer->active_package->name ?? 'No Active Package' }}">
                                {{ $customer->active_package->name ?? 'No Active Package' }}
                            </span>
                        </div>

                        {{-- Baris 3: Nomor Telepon & Harga Paket --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted text-monospace text-truncate" style="max-width: 60%;">
                                <i class="fas fa-phone-alt fa-xs mr-1"></i> {{ $customer->contact }}
                            </small>
                            <small class="font-weight-bold text-dark">
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
                            <small class="text-muted text-truncate-2" style="max-width: 100%;">
                                <i class="fas fa-map-marker-alt fa-xs mr-1"></i> {{ $customer->address }}
                            </small>
                        </div>

                        {{-- Garis Pembatas --}}
                        <hr class="my-2" />

                        {{-- Baris 5: Penanggung Jawab Admin + Dropdown Action Kompatibel Desktop --}}
                        <div
                            class="d-flex justify-content-between align-items-center bg-light px-2 py-1 rounded border">
                            <small class="text-muted font-weight-600">
                                <i class="fas fa-user-shield mr-1"></i> {{ $customer->user?->name ?? 'No Admin' }}
                            </small>

                            @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                                <div class="dropdown">
                                    <button class="btn btn-xs btn-outline-primary dropdown-toggle px-2" type="button"
                                        id="mobileDropdown-{{ $customer->slug }}" data-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="fas fa-cog"></i>
                                    </button>

                                    <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in p-1"
                                        aria-labelledby="mobileDropdown-{{ $customer->slug }}">

                                        @can('customer-edit')
                                            <a class="dropdown-item py-2"
                                                href="{{ route('customers.edit', $customer->slug) }}">
                                                <i class="far fa-edit mr-2 text-primary"></i>
                                                {{ __('customer.action.edit') }}
                                            </a>
                                        @endcan

                                        @can('customer-edit')
                                            @if (!$customer->activation)
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('customers.activation', $customer->slug) }}">
                                                    @csrf @method('put')
                                                    <a class="dropdown-item py-2" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check mr-2 text-success"></i>
                                                        {{ __('customer.action.activation') }}
                                                    </a>
                                                </form>
                                            @else
                                                @if (!$customer->is_active)
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.enable', $customer->slug) }}">
                                                        @csrf @method('put')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i class="fa fa-check mr-2 text-success"></i>
                                                            {{ __('pppoe.secret.enable') }}
                                                        </a>
                                                    </form>
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.forceEnable', $customer->slug) }}">
                                                        @csrf @method('put')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i class="fa fa-check mr-2 text-success"></i>
                                                            {{ __('pppoe.secret.force-enable') }}
                                                        </a>
                                                    </form>
                                                @elseif (!$customer->is_active)
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.disable', $customer->slug) }}">
                                                        @csrf @method('put')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i class="fa fa-times text-warning mr-2"></i>
                                                            {{ __('pppoe.secret.disable') }}
                                                        </a>
                                                    </form>
                                                    <form method="POST" class="d-inline"
                                                        action="{{ route('customers.forceDisable', $customer->slug) }}">
                                                        @csrf @method('put')
                                                        <a class="dropdown-item py-2" href="#"
                                                            onclick="event.preventDefault(); this.closest('form').submit();">
                                                            <i class="fa fa-times text-warning mr-2"></i>
                                                            {{ __('pppoe.secret.force-disable') }}
                                                        </a>
                                                    </form>
                                                @endif
                                            @endif
                                        @endcan

                                        <a class="dropdown-item py-2"
                                            href="{{ route('customers.resend-activation', $customer->slug) }}">
                                            <i class="fas fa-paper-plane mr-2 text-info"></i>
                                            {{ __('customer.action.resend-activation') }}
                                        </a>

                                        @can('secret-edit')
                                            <form method="POST"
                                                action="{{ route('customers.pppoe.export', $customer->slug) }}">
                                                @csrf @method('PUT')
                                                <a class="dropdown-item py-2" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fas fa-file-export mr-2 text-secondary"></i>
                                                    {{ __('pppoe.secret.export') }}
                                                </a>
                                            </form>
                                        @endcan

                                        @can('customer-delete')
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item py-2 text-danger font-weight-bold"
                                                href="{{ route('customers.delete', $customer->slug) }}">
                                                <i class="fa fa-ban mr-2 text-danger"></i>
                                                {{ __('customer.action.suspend') }}
                                            </a>
                                        @endcan

                                    </div>
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
                    theme: 'default',
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
        <!-- CSS Select2 -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <!-- Tema Bootstrap 4 untuk Select2 agar tampilannya pas dengan template SB Admin / Bootstrap Anda -->
        <link href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css"
            rel="stylesheet" />
        <style>
            .custom-badge {
                background-color: #d1e7dd;
                /* Soft green */
                color: #0f5132;
                border-radius: 0.25rem;
                padding: 0.25em 0.6em;
                font-size: 0.75rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: inline-block;
            }

            .btn-disabled {
                opacity: 0.3 !important;
                pointer-events: none !important;
            }

            .bg-turquoise-soft {
                background-color: #e0f7f4;
                /* warna hijau tosca yang lembut */
            }

            .text-turquoise {
                color: #009688;
                /* warna hijau tosca */
            }

            .bg-gray-soft {
                background-color: lightgray !important;
                /* abu-abu soft sedang */
            }

            /* 1. Menyamakan tinggi container luar Select2 */
            .select2-container .select2-selection--single {
                height: calc(1.5em + 0.75rem + 2px) !important;
                border: 1px solid #d1d3e2 !important;
                /* Warna border standar SB Admin / Bootstrap 4 */
                border-radius: 0.35rem !important;
                /* Kelengkungan sudut form standar */
                background-color: #fff !important;
                display: flex !important;
                align-items: center !important;
                /* Memastikan isi seleksi vertikal di tengah */
            }

            /* 2. Mengatur posisi teks yang dipilih di dalam dropdown */
            .select2-container--default .select2-selection--single .select2-selection__rendered {
                color: #6e707e !important;
                /* Warna teks standar Bootstrap 4 */
                padding-left: 0.75rem !important;
                /* Padding kiri disamakan dengan form-control */
                line-height: normal !important;
                /* Reset line-height agar fleksibel */
                width: 100% !important;
            }

            /* 3. Menyelaraskan posisi vertikal tombol silang (clear button) jika allowClear: true */
            .select2-container--default .select2-selection--single .select2-selection__clear {
                margin-right: 0.5rem !important;
                height: 100% !important;
                display: flex !important;
                align-items: center !important;
            }

            /* 4. Menyelaraskan posisi vertikal ikon panah dropdown Select2 */
            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 100% !important;
                /* Mengikuti tinggi penuh dari komponen induk */
                top: 0 !important;
                /* Reset posisi atas */
                right: 0.5rem !important;
                /* Jarak panah dari kanan */
                display: flex !important;
                align-items: center !important;
                /* Panah otomatis presisi di tengah vertikal */
            }

            /* 5. Menyesuaikan tampilan kotak drop-down yang melayang saat diklik */
            .select2-dropdown {
                border: 1px solid #d1d3e2 !important;
                border-radius: 0.35rem !important;
                z-index: 1060 !important;
                /* Mencegah tertutup komponen modal atau layout wrapper */
            }

            /* Tampilan Badges bawaan Anda tetap dipertahankan di bawah ini */
            .custom-badge {
                background-color: #d1e7dd;
                color: #0f5132;
                border-radius: 0.25rem;
                padding: 0.25em 0.6em;
                font-size: 0.75rem;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
                display: inline-block;
            }

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
