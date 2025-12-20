<x-layouts.cpanel.customers.app-layout title="Customers">
    <div class="card-header py-3">
        <h5 class="m-0 font-weight-bold text-primary mb-2">
            {{ __('customer.restore.header.index') }}
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
                    {{ $data->total() }} customers found
                </div>
            @endif
        </span>
        <span>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
                aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;">
                <i class="fas fa-search"></i>
                <span>{{ __('billing.header.search') }}</span>
            </a>
            <div class="collapse" id="collapseSearchCustomer">
                <div class="card-header py-3 rounded">
                    <form>
                        <div class="row">
                            <!-- Kolom Pencarian Nama -->
                            <div class="col-md-3 col-12 mb-2">
                                <input type="search" class="form-control"
                                    placeholder="{{ __('customer.restore.placeholder.name') }}" name="search_name">
                            </div>
                            <!-- Kolom Alamat -->
                            <div class="col-md-3 col-12 mb-2">
                                <select name="search_address" class="form-control">
                                    <option value="">{{ __('customer.restore.placeholder.select-address') }}
                                    </option>
                                    <option value="NULL">{{ __('customer.restore.placeholder.without-address') }}
                                    </option>
                                    @foreach ($address as $addrr)
                                        <option value="{{ $addrr }}">{{ $addrr }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Kolom Status -->
                            <div class="col-md-2 col-12 mb-2">
                                <select name="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="false">Enable</option>
                                    <option value="true">Disable</option>
                                    <option value="isolir">Isolir</option>
                                    <option value="offline">Offline</option>
                                    <option value="online">Online</option>
                                    <option value="free">Free</option>
                                    <option value="deactivated">Deactivated</option>
                                </select>
                            </div>
                            <!-- Kolom Admin -->
                            @canany(['view-customers-widget-total'])
                                <div class="col-md-2 col-12 mb-2">
                                    {!! Form::select('search_admin', ['NULL' => 'Not Linked User'] + $allAdmin->toArray(), null, [
                                        'placeholder' => __('customer.restore.placeholder.all-responsibility') ?? 'All Admin',
                                        'class' => 'form-control',
                                        'id' => 'admin-dropdown',
                                    ]) !!}
                                </div>
                            @endcanany
                            <!-- Kolom Server -->
                            <div class="col-md-2 col-12 mb-2">
                                {!! Form::select('server_search', $allServers, null, [
                                    'placeholder' => 'All Server',
                                    'class' => 'form-control',
                                    'id' => 'server-dropdown',
                                ]) !!}
                            </div>
                            <!-- Kolom Package -->
                            <div class="col-md-2 col-12 mb-2">
                                <select name="search_package" id="package-dropdown" class="form-control">
                                    <option value="">All Package</option>
                                    @foreach ($allPackages as $pkg)
                                        <option value="{{ $pkg->id }}">{{ $pkg->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Tombol Pencarian -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-search"></i>
                                    {{ __('customer.restore.button.search') }}
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
                    <span class="font-weight-bold text-primary">Show</span>
                    <select name="perPage" id="perPage" class="form-control" onchange="updatePerPage()"
                        style="width: auto; min-width: 70px; height: 35px; padding: 2px 8px; text-align: center; margin-right: 10px; margin-left: 10px;">
                        <option value="25" {{ request('perPage') == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ request('perPage') == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ request('perPage') == 100 ? 'selected' : '' }}>100</option>
                    </select>
                </div>

                <!-- Export Button -->
                <a href="{{ route('customers.export') }}" class="btn btn-success btn-sm d-flex align-items-center"
                    style="height: 35px;">
                    <i class="fas fa-file-excel" style="margin-right: 5px;"></i> Export
                </a>
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
                        <th width="80px">
                            <span class="badge bg-danger-soft text-primary">
                                {{ __('customer.table.action') }}
                            </span>
                        </th>
                    </tr>
                    @foreach ($data as $key => $customer)
                        <tr class="@if ($customer->disabled === 'true') text-secondary @else text-primary @endif">
                            <td>
                                <span class="badge bg-danger-soft">
                                    {{ ++$i }}
                                </span>
                            </td>
                            <td>
                                @can('customer-list')
                                    <a class="@if ($customer->disabled === 'true') text-secondary @else text-primary @endif"
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
                                    @if ($customer->activation == 'true')
                                        {{ Carbon\Carbon::parse($customer->activation_date)->translatedFormat('d F Y') ?? 'No Data' }}
                                    @else
                                        <i class="text-danger">{{ __('Not Activated') }}</i>
                                    @endif
                            </td>
                            </span>
                            <td>
                                <span class="badge bg-danger-soft">
                                    <i class="fas fa-chalkboard"></i>
                                    {{ $customer->last_package->name ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-danger-soft">
                                    @moneyIDR(optional($customer->last_package)->price - optional($customer->customerMembership)->discount_value)
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-danger-soft">
                                    <i class="fas fa-user-shield"></i>
                                    {{ $customer->user?->name ?? 'No Linked User' }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-dark text-white">
                                    <i class="fas fa-trash-alt me-1"></i>
                                    {{ __('customer.info.deleted') ?? 'Deleted' }}
                                </span>
                            </td>
                            <td class="items-center justify-content-end">
                                @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                                    <x-drop-down-action>
                                        @can('customer-edit')
                                            <li>
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('customers.restore', $customer->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="dropdown-item" href="#"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fas fa-undo-alt me-2 text-success"></i>
                                                        {{ __('customer.action.restore') }}
                                                    </a>
                                                </form>
                                            </li>
                                        @endcan
                                        @can('customer-delete')
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('customers.forceDelete', $customer->slug) }}"
                                                    class="d-inline">
                                                    @csrf
                                                    @method('DELETE') {{-- spoofing DELETE method --}}
                                                    <a href="#" class="dropdown-item text-danger"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fas fa-trash-alt me-2 text-danger"></i>
                                                        {{ __('customer.action.force-delete') }}
                                                    </a>
                                                </form>
                                            </li>
                                        @endcan
                                    </x-drop-down-action>
                                @endcanany
                            </td>
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
                <div class="card mb-3">
                    <div class="card-body p-2">

                        {{-- Baris 1: #No dan status --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-secondary-soft">
                                #{{ $data->firstItem() + $loop->index }}
                            </span>
                            @if ($customer->activation == 'false')
                                <span class="custom-badge text-white bg-danger">
                                    <i class="fa fa-times text-white me-2"></i>
                                    {{ __('customer.info.not-activation') }}
                                </span>
                            @else
                                @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span
                                        class="custom-badge text-white @if ($customer->disabled === 'false') bg-success @endif">
                                        <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                                    </span>
                                @endif
                                @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span
                                        class="custom-badge text-white @if ($customer->disabled === 'false') bg-secondary @endif">
                                        <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                                    </span>
                                @endif
                            @endif
                        </div>

                        {{-- Baris 2: Nama + Nama Package --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0" style="max-width: 60%">
                                @can('customer-list')
                                    <a class="text white {{ $customer->disabled === 'true' ? 'text-secondary' : 'text-primary' }}"
                                        href="{{ route('customers.show', $customer->slug) }}">
                                        {{ $customer->name }}
                                    </a>
                                @else
                                    {{ $customer->name }}
                                @endcan
                            </h6>
                            <span class="custom-badge text-truncate bg-turquoise-soft text-turquoise"
                                style="max-width: 50%;" title="{{ $customer->last_package->name ?? 'No Package' }}">
                                {{ $customer->last_package->name ?? 'No Package' }}
                            </span>
                        </div>

                        {{-- Baris 3: Nomor Telepon & Force Action --}}
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted text-truncate" style="max-width: 60%;">
                                {{ $customer->contact }}
                            </small>
                            <small class="fw-semibold">
                                @moneyIDR(optional($customer->last_package)->price - optional($customer->customerMembership)->discount_value)
                            </small>
                        </div>

                        {{-- Baris 4: Alamat + Harga --}}
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <small class="text-muted" style="max-width: 100%;">{{ $customer->address }}</small>
                        </div>

                        {{-- Garis pembatas --}}
                        <hr class="my-2" />

                        {{-- Baris 4: Admin + Dropdown Action --}}
                        <div class="d-flex justify-content-between align-items-center bg-gray-soft px-2 py-1 rounded">
                            <small class="text-muted">{{ $customer->user?->name ?? 'No Admin' }}</small>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                                    id="dropdownMenuButton" data-bs-toggle="dropdown">
                                    ⋮
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('customer-edit')
                                        <form method="POST" class="d-inline"
                                            action="{{ route('customers.restore', $customer->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fas fa-undo-alt me-2 text-success"></i>
                                                {{ __('customer.action.restore') }}
                                            </a>
                                        </form>
                                    @endcan
                                    <div class="dropdown-divider"></div>
                                    @can('customer-delete')
                                        <form method="POST"
                                            action="{{ route('customers.forceDelete', $customer->slug) }}"
                                            class="d-inline">
                                            @csrf
                                            @method('DELETE') {{-- spoofing DELETE method --}}
                                            <a href="#" class="dropdown-item text-danger"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fas fa-trash-alt me-2 text-danger"></i>
                                                {{ __('customer.action.force-delete') }}
                                            </a>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            @endforeach

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <!-- Pagination -->
                <div>
                    {{ $data->appends(['perPage' => request('perPage')])->links('vendor.pagination.custom') }}
                </div>
            </div>
        </div>
    </div>

    @section('custom_scripts')
        <script>
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
        </style>
    @endsection


</x-layouts.cpanel.customers.app-layout>
