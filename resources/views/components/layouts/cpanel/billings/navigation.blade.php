<!-- Nav Item - Dashboard -->
<li class="nav-item @if (request()->routeIs('home')) active @endif">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('navigation.home.nav-item') }}</span>
    </a>
</li>


<!-- Nav Item - Tables -->

<!-- Customers -->
@canany(['customer-list', 'customer-create', 'customer-edit', 'customer-delete', 'customer-trashed'])
    <li class="nav-item @if (request()->routeIs('customers*')) active @endif">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomer">
            <i class="fas fa-users"></i>
            <span>{{ __('navigation.customers.nav-item') }}</span>
        </a>
        <div id="collapseCustomer" class="collapse @if (request()->routeIs('customers*')) show @endif"
            data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('customer-create')
                    <a class="collapse-item @if (request()->routeIs('customers.create')) active @endif"
                        href="{{ route('customers.create') }}">
                        {{ __('navigation.customers.create') }}
                    </a>
                @endcan
                @can('customer-list')
                    <a class="collapse-item @if (request()->routeIs('customers.index')) active @endif"
                        href="{{ route('customers.index') }}">
                        {{ __('navigation.customers.index') }}
                    </a>
                @endcan
                @can('customer-trashed')
                    <a class="collapse-item @if (request()->routeIs('customers.trashed')) active @endif"
                        href="{{ route('customers.trashed') }}">
                        {{ __('navigation.customers.trashed') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<hr class="sidebar-divider">

<!-- ======================
         TRANSACTIONAL
    ======================= -->

<!-- Billing -->
@canany(['billing-list', 'billing-create', 'billing-edit'])
    <li class="nav-item @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) active @endif">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseBilling">
            <i class="fas fa-layer-group"></i>
            <span>{{ __('navigation.billings.nav-item') }}</span>
        </a>
        <div id="collapseBilling" class="collapse @if (request()->routeIs('billings*') || request()->routeIs('billingHistory*')) show @endif"
            data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                @can('billing-create')
                    <a class="collapse-item @if (request()->routeIs('billings.create')) active @endif"
                        href="{{ route('billings.create') }}">
                        {{ __('navigation.billings.create') }}
                    </a>
                @endcan
                @can('billing-list')
                    <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                        href="{{ route('billings.index') }}">
                        {{ __('navigation.billings.active_billing') }}
                    </a>
                    <a class="collapse-item @if (request()->routeIs('billingHistory.index')) active @endif"
                        href="{{ route('billingHistory.index') }}">
                        {{ __('navigation.billings.history') }}
                    </a>
                @endcan
            </div>
        </div>
    </li>
@endcanany

<!-- Reports -->
@canany(['report-view'])
    <li class="nav-item @if (request()->routeIs('reports*')) active @endif">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReports">
            <i class="fas fa-chart-bar"></i>
            <span>{{ __('navigation.reports.nav-item') }}</span>
        </a>
        <div id="collapseReports" class="collapse @if (request()->routeIs('reports*')) show @endif"
            data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('reports.kpi')) active @endif"
                    href="{{ route('reports.kpi') }}">
                    {{ __('navigation.reports.kpi.index') }}
                </a>
                <a class="collapse-item @if (request()->routeIs('reports.internet.monthly')) active @endif"
                    href="{{ route('reports.internet.monthly') }}">
                    {{ __('navigation.reports.internet-monthly.index') }}
                </a>
            </div>
        </div>
    </li>
@endcanany

<hr class="sidebar-divider">

{{-- ======================
    RECEIPTS
====================== --}}
@canany(['receipt-list'])
    <li class="nav-item @if (request()->routeIs('receipts*')) active @endif">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseReceipt">
            <i class="fas fa-receipt"></i>
            <span>{{ __('navigation.receipts.nav-item') }}</span>
        </a>

        <div id="collapseReceipt" class="collapse @if (request()->routeIs('receipts*')) show @endif"
            data-bs-parent="#accordionSidebar">

            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('receipts.index')) active @endif"
                    href="{{ route('receipts.index') }}">
                    {{ __('navigation.receipts.index') ?? 'Receipts' }}
                </a>
            </div>
        </div>
    </li>
@endcanany

@if (request()->routeIs('receipts.index'))
    <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseSearchCustomer"
            aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;">
            <i class="fas fa-search"></i>
            <span>{{ __('billing.header.search') }}</span>
        </a>
        <div class="collapse" id="collapseSearchCustomer">
            <div class="card-header py-3 rounded">
                <form>
                    <div class="row align-items-center mb-1">
                        <input type="text" name="search_name" class="form-control" placeholder="{{ __('billing.placeholder.search-customer-name') }}" value="{{ request('search_name') }}">
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_address" class="form-control">
                            <option value="">{{ __('billing.placeholder.search-address') }}</option>
                            <option value="NULL">{{ __('billing.placeholder.search-without-address') }}</option>
                            {{-- Gunakan ?? [] untuk keamanan --}}
                            @foreach ($address ?? [] as $addrr)
                                <option value="{{ $addrr }}">{{ $addrr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_month" class="form-control">
                            <option value="">{{ __('billing.placeholder.search-month') }}</option>
                            @foreach ($months ?? [] as $k => $v)
                                <option value="{{ $k }}" {{ request('search_month') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_year" class="form-control">
                            <option value="">{{ __('billing.placeholder.search-year') }}</option>
                            @foreach ($years ?? [] as $k => $v)
                                <option value="{{ $k }}" {{ request('search_year') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="payment_methode" class="form-control" id="payment-methode-dropdown">
                            <option value="">{{ __('billing.placeholder.search-payment-methode') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="transfer">{{ __('Transfer') }}</option>
                            <option value="online">{{ __('Online') }}</option>
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="account_bank" class="form-control d-none" id="account-bank-dropdown">
                            <option value="">{{ __('customer.placeholder.search-account-bank') }}</option>
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="teller" class="form-control">
                            <option value="">{{ __('billing.placeholder.search-teller') }}</option>
                            @foreach ($tellers ?? [] as $k => $v)
                                <option value="{{ $k }}" {{ request('teller') == $k ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
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
</div>


@section('custom_scripts')
    <script src="{{ asset('js/flatpickr') }}"></script>
    <script>
        config = {
            enableTime: false,
            dateFormat: 'd F Y',
            //format:'DD/MM/YYYY HH:mm:ss'
        }
        flatpickr("input[type=datetime-local]", config);
    </script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#search-status-dropdown').on('change', function() {
                var searchStatusValue = this.value;
                $("#select-date").html('');
                if (searchStatusValue == "") {
                    $('#select-date').addClass('d-none');
                } else if (searchStatusValue == "!LS") {
                    $('#select-date').removeClass('d-none');
                    $('#select-date').attr("placeholder", "Due Date");
                } else if (searchStatusValue == "LS") {
                    $('#select-date').removeClass('d-none');
                    $('#select-date').attr("placeholder", "Payment Time");
                } else if (searchStatusValue == "PL") {
                    $('#select-date').removeClass('d-none');
                    $('#select-date').attr("placeholder", "Pay Later Time");
                }

            });
            //  }
            /*==================================================
                Server Dropdown Change Event
            ====================================================*/
            $('#payment-methode-dropdown').on('change', function() {
                var paymentMethode = this.value;
                const url = "{{ route('fetch.account.bank') }}";
                $("#account-bank-dropdown").html('');
                if (paymentMethode == "transfer") {
                    $('#account-bank-dropdown').removeClass('d-none');
                } else {
                    $('#account-bank-dropdown').addClass('d-none');
                }
                $.ajax({
                    url: url,
                    type: "POST",
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(result_server) {
                        $('#account-bank-dropdown').html('<option value="">All Bank</option>');
                        $.each(result_server.banks, function(key, value) {
                            $("#account-bank-dropdown").append('<option value="' + value
                                .name + '#' + value.bank + '#' + value
                                .account_number + '">' + value.bank + ' (' + value
                                .name + ' - ' + value.account_number + ')' +
                                '</option>');
                        });
                    }
                });
            });
        });
    </script>
@endsection
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth < 768) {
                document
                    .querySelectorAll('#accordionSidebar .collapse.show')
                    .forEach(el => el.classList.remove('show'));
            }
        });
    </script>
@endpush
<!-- End of Sidebar -->
