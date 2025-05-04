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
                                    placeholder="{{ __('customer.placeholder.name') }}" name="search_name">
                            </div>
                            <!-- Kolom Alamat -->
                            <div class="col-md-3 col-12 mb-2">
                                <select name="search_address" class="form-control">
                                    <option value="">{{ __('customer.placeholder.select-address') }}</option>
                                    <option value="NULL">{{ __('customer.placeholder.without-address') }}</option>
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
                                        'placeholder' => __('customer.placeholder.all-responsibility') ?? 'All Admin',
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
                            <!-- Kolom Paket -->
                            <div class="col-md-2 col-12 mb-2">
                                <select name="search_paket" id="paket-dropdown" class="form-control d-none">
                                    <option value="">All Paket</option>
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


    <div class="card-header py-3">
        @can('customer-create')
            <span><a class="btn btn-sm btn-success mt-2" href="{{ route('customers.create') }}"><i class="fa fa-plus"></i>
                    Add New Customer </a></span>
        @endcan
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
                    <i class="fas fa-file-excel" style="margin-right: 5px;"></i> Export to Excel
                </a>
            </form>
        </div>
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
                                <i class="fas fa-chalkboard"></i> {{ $customer->paket->name }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-danger-soft">
                                @if ($customer->billing_option === 1 || ($customer->billing_option === 2 && $customer->billing_period === $periodeName))
                                    @moneyIDR(optional($customer->paket)->price - optional($customer->member)->discount)
                                @else
                                    @moneyIDR(optional($customer->paket)->price)
                                @endif
                            </span>
                        </td>

                        <td>
                            <span class="badge bg-danger-soft">
                                <i class="fas fa-user-shield"></i> {{ $customer->user?->name ?? 'No Linked User' }}
                            </span>
                        </td>

                        <td>
                            @if ($customer->activation == 'false')
                                <span class="badge text-white bg-danger">
                                    <i class="fa fa-times text-warning me-2"></i>
                                    {{ __('customer.info.not-activation') }}
                                </span>
                            @else
                                @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                    <span class="badge text-white @if ($customer->disabled === 'false') bg-success @endif">
                                        <i class="fas fa-lock-open"></i> {{ __('pppoe.secret.enabled') }}
                                    </span>
                                @endif
                                @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                    <span class="badge @if ($customer->disabled === 'false') bg-secondary @endif">
                                        <i class="fas fa-lock"></i> {{ __('pppoe.secret.disabled') }}
                                    </span>
                                @endif
                        </td>
                @endif
                <td class="items-center justify-content-end">
                    @canany(['secret-disable', 'customer-edit', 'secret-edit', 'customer-delete'])
                        <x-drop-down-action>
                            @can('customer-edit')
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.edit', $customer->slug) }}">
                                        <i class="far fa-edit me-2 text-success"></i>
                                        {{ __('customer.action.edit') }}
                                    </a>
                                </li>
                                <li>
                                    @if ($customer->activation == 'false')
                                        <form method="POST" class="d-inline"
                                            action="{{ route('customers.activation', $customer->slug) }}">
                                            @csrf
                                            @method('put')
                                            <a class="dropdown-item" href="#"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fa fa-check me-2 text-success"></i>
                                                {{ __('customer.action.activation') }}
                                            </a>
                                        </form>
                                    @else
                                        @if ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_DISABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('customers.enable', $customer->slug) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('pppoe.secret.enable') }}
                                                </a>
                                            </form>
                                            <form method="POST" class="d-inline"
                                                action="{{ route('customers.forceEnable', $customer->slug) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-check me-2 text-success"></i>
                                                    {{ __('pppoe.secret.force-enable') }}
                                                </a>
                                            </form>
                                        @elseif ($customer->disabled === \App\Enums\MikrotikStatus::PPPOE_ENABLE->value)
                                            <form method="POST" class="d-inline"
                                                action="{{ route('customers.disable', $customer->slug) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('pppoe.secret.disable') }}
                                                </a>
                                            </form>
                                            <form method="POST" class="d-inline"
                                                action="{{ route('customers.forceDisable', $customer->slug) }}">
                                                @csrf
                                                @method('put')
                                                <a class="dropdown-item" href="#"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fa fa-times text-warning me-2"></i>
                                                    {{ __('pppoe.secret.force-disable') }}
                                                </a>
                                            </form>
                                        @endif
                                    @endif
                                </li>
                            @endcan
                            @can('customer-delete')
                                <li>
                                    <a class="dropdown-item" href="{{ route('customers.delete', $customer->slug) }}"><i
                                            class="fa fa-trash me-2 text-danger"></i>&nbsp{{ __('customer.action.delete') }}</a>
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

</x-layouts.cpanel.customers.app-layout>

<script>
    function updatePerPage() {
        let perPage = document.getElementById('perPage').value;
        let url = new URL(window.location.href);
        url.searchParams.set('perPage', perPage);
        window.location.href = url.toString();
    }
</script>
