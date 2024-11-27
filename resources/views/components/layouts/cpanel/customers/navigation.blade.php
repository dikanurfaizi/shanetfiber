<!-- Nav Item - Dashboard -->
<li class="nav-item @if (request()->routeIs('home')) active @endif">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('Home') }}</span>
    </a>
</li>


<!-- Nav Item - Tables -->

@canany(['customer-list', 'customer-edit', 'customer-delete', 'customer-create', 'secret-edit'])
    <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseCustomer" aria-expanded="false"
            aria-controls="collapseCustomer" style="padding-top: inherit;">
            <i class="fas fa-fw fa-users"></i>
            <span>{{ __('navigation.customers.nav-item') }}</span>
        </a>
        <div id="collapseCustomer" class="collapse" aria-labelledby="headingCustomer" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('customer-create')
                    <a class="collapse-item @if (request()->routeIs('customers.create')) active @endif"
                        href="{{ route('customers.create') }}">{{ __('navigation.customers.create') }}</a>
                @endcan
                <a class="collapse-item @if (request()->routeIs('customers.index')) active @endif"
                    href="{{ route('customers.index') }}">{{ __('navigation.customers.index') }}</a>
            </div>
        </div>
    </li>
@endcanany

@canany(['billing-list', 'billing-create', 'billing-edit', 'billing-delete'])
    <li class="nav-item @if (request()->routeIs('billings*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBilling"
            aria-expanded="false" aria-controls="collapseBilling" style="padding-top: inherit;">
            <i class="fas fa-money-check-alt"></i>
            <span>{{ __('navigation.billings.nav-item') }}</span>
        </a>
        <div id="collapseBilling" class="collapse" aria-labelledby="headingBilling" data-parent="#accordionSidebar">
            @can('billing-create')
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item @if (request()->routeIs('billings.create')) active @endif"
                        href="{{ route('billings.create') }}">{{ __('navigation.billings.create') }}</a>
                    <a class="collapse-item @if (request()->routeIs('billings.create.one')) active @endif"
                        href="{{ route('billings.create.one') }}">{{ __('navigation.billings.add') }}</a>
                </div>
            @endcan
            @canany(['billing-list', 'billing-edit', 'billing-delete'])
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                        href="{{ route('billings.index') }}">{{ __('navigation.billings.last') }}</a>
                </div>
            @endcanany
        </div>
    </li>
@endcanany
@canany(['user-list', 'user-create', 'user-edit', 'user-delete', 'role-list', 'role-edit', 'role-delete', 'server-list',
    'server-edit', 'server-delete', 'server-create'])
    <!-- Divider -->
    <hr class="sidebar-divider">
    <li class="nav-item">
        <a class="nav-link" href="{{ route('websystem.edit', 'websystem') }}">
            <i class="fas fa-tools"></i>
            <span><strong>{{ __('Settings') }}</strong></span>
        </a>
    </li>
@endcanany



<!-- Divider -->
{{-- <hr class="sidebar-divider">
@if (request()->routeIs('customers.index'))
    <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
            aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;"
            style="padding-top: inherit;">
            <i class="fas fa-search"></i>
            <span>{{ __('billing.header.search') }}</span>
        </a>
        <div class="collapse show" id="collapseSearchCustomer">
            <div class="card-header py-3 rounded">
                <form>
                    <div class="row align-items-center mb-1">
                        <input type="search" class="form-control" placeholder="{{ __('customer.placeholder.name') }}"
                            name="search_name">
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_address" class="form-control">
                            <option value="">{{ __('customer.placeholder.select-address') }}</option>
                            <option value="NULL">{{ __('customer.placeholder.without-address') }}</option>
                            @foreach ($address as $addrr)
                                <option value="{{ $addrr }}">{{ $addrr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
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
                    <div class="row align-items-center mb-1">
                        <select name="search_service" class="form-control">
                            <option value="">{{ __('customer.placeholder.all-internet-service') }}</option>
                            <option value="ppp">{{ __('customer.placeholder.ppp') }}</option>
                            <option value="ip_static">{{ __('customer.placeholder.ip-static') }}</option>

                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        {!! Form::select('server_search', $allServers, null, [
                            'placeholder' => 'All Server',
                            'class' => 'form-control',
                            'id' => 'server-dropdown',
                        ]) !!}
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_paket" id="paket-dropdown" class="form-control d-none">
                            <option value="">All Paket</option>
                        </select>
                    </div>

                    <div class="row align-items-center mt-3">
                        <span>
                            <button class="btn btn-success btn-sm" type="submit"><i class="fas fa-search"></i>
                                {{ __('customer.button.search') }}</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </li>
@endif
<div class="text-center d-none d-md-inline pt-4">
    <button class="rounded-circle border-0" id="sidebarToggle"></button>
</div> --}}


@section('custom_scripts')
    <script type="text/javascript">
        $(document).ready(function() {

            /*==================================================
                Server Dropdown Change Event
            ====================================================*/
            let idServer;
            $('#server-dropdown').on('change', function() {
                // var idServer = this.value;
                idServer = this.value;
                const url = "{{ route('fetch.paket') }}";
                $("#paket-dropdown").html('');
                if (idServer) {
                    $('#paket-dropdown').removeClass('d-none');
                } else {
                    $('#paket-dropdown').addClass('d-none');
                }
                $.ajax({

                    url: url,
                    type: "POST",
                    data: {
                        mikrotik_id: idServer,
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(result_server) {
                        $('#paket-dropdown').html('<option value="">Select Paket</option>');
                        $.each(result_server.pakets, function(key, value) {
                            $("#paket-dropdown").append('<option value="' + value.id +
                                '">' + value.name + ' - ' + 'Rp. ' + new Intl
                                .NumberFormat().format(value.price) + ',00' +
                                '</option>');
                        });
                        // $("#paket-dropdown").append('<option value="free">Free</option>');
                    }

                });
            });

            // $('#paket-dropdown').on('change', function() {
            //     var service = this.value;
            //     $("#ppp_types_id").html('');
            //     if (service) {
            //          $('#ppp_types_id').removeClass('d-none');
            //      } else {
            //         $('#ppp_types_id').addClass('d-none');
            //      }

            //   });

            $('#service-dropdown').on('change', function() {
                var idService = this.value;
                if (idService == 'ppp') {
                    $("#input-ppp").removeClass('d-none');
                    $("#input-ip-static").addClass('d-none');
                } else if (idService == 'ip_static') {
                    $("#input-ppp").addClass('d-none');
                    $("#input-ip-static").removeClass('d-none');

                    $("#interface-dropdown").html('');
                    const url_interface = "{{ route('fetch.interface') }}";
                    $.ajax({

                        url: url_interface,
                        type: "POST",
                        data: {
                            mikrotik_id: idServer,
                            _token: '{{ csrf_token() }}'
                        },
                        dataType: 'json',
                        success: function(result_server) {
                            $('#interface-dropdown').html(
                                '<option value="">Select Interface</option>');
                            $.each(result_server, function(key, value) {
                                $("#interface-dropdown").append('<option value="' +
                                    value.name + '">' + value.name + '</option>');
                            });
                        }

                    });

                } else {
                    $("#input-ppp").addClass('d-none');
                    $("#input-ip-static").addClass('d-none');
                }

            });
        });
    </script>
@endsection
<!-- End of Sidebar -->
