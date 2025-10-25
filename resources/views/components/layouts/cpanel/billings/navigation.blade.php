<!-- Nav Item - Dashboard -->
<li class="nav-item @if (request()->routeIs('home')) active @endif">
    <a class="nav-link" href="{{ route('home') }}">
        <i class="fas fa-home"></i>
        <span>{{ __('Home') }}</span>
    </a>
</li>


<!-- Nav Item - Tables -->

@canany(['billing-list', 'billing-create','billing-edit', 'billing-delete'])
    <li class="nav-item @if (request()->routeIs('billings*') || request()->routeIs('receipts*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseBilling" aria-expanded="false"
            aria-controls="collapseBilling" style="padding-top: inherit;">
            <i class="fas fa-file-invoice-dollar"></i>
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
            @endcanany
            @canany(['billing-list'])
                <div class="bg-white py-2 collapse-inner rounded">
                    <a class="collapse-item @if (request()->routeIs('billings.index')) active @endif"
                        href="{{ route('billings.index') }}">
                        {{ __('navigation.billings.active_billing') }}</a>
                    <a class="collapse-item @if (request()->routeIs('billingHistory.index')) active @endif"
                        href="{{ route('billingHistory.index') }}">
                        {{ __('navigation.billings.history') }}</a>
                </div>
            @endcanany
        </div>
    </li>
@endcanany

@canany(['receipt-list'])
    <li class="nav-item @if (request()->routeIs('receipts*') || request()->routeIs('receipts*')) active @endif">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseReceipt"
            aria-expanded="false" aria-controls="collapseReceipt" style="padding-top: inherit;">
            <i class="fas fa-receipt"></i>
            <span>{{ __('navigation.receipts.nav-item') }}</span>
        </a>
        <div id="collapseReceipt" class="collapse" aria-labelledby="headingReceipt" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item @if (request()->routeIs('receipts*')) active @endif"
                    href="{{ route('receipts.index') }}">{{ __('Receipts') }}</a>
            </div>
        </div>
    </li>
@endcanany

@if (request()->routeIs('receipts.index'))
    <li class="nav-item active">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
            aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;"
            style="padding-top: inherit;">
            <i class="fas fa-search"></i>
            <span>{{ __('billing.header.search') }}</span>
        </a>
        <div class="collapse" id="collapseSearchCustomer">
            <div class="card-header py-3 rounded">
                <form>
                    <div class="row align-items-center mb-1">
                        {!! Form::text('search_name', null, [
                            'placeholder' => __('billing.placeholder.search-customer-name'),
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="row align-items-center mb-1">
                        <select name="search_address" class="form-control">
                            <option value="">{{ __('billing.placeholder.search-address') }}</option>
                            <option value="NULL">{{ __('billing.placeholder.search-without-address') }}</option>
                            @foreach ($address as $addrr)
                                <option value="{{ $addrr }}">{{ $addrr }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row align-items-center mb-1">
                        {!! Form::select('search_month', $months, null, [
                            'placeholder' => __('billing.placeholder.search-month'),
                            'class' => 'form-control',
                        ]) !!}
                    </div>
                    <div class="row align-items-center mb-1">
                        {!! Form::select('search_year', $years, null, [
                            'placeholder' => __('billing.placeholder.search-year'),
                            'class' => 'form-control',
                        ]) !!}
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
                        {!! Form::select('teller', $tellers, null, [
                            'placeholder' => __('billing.placeholder.search-teller'),
                            'class' => 'form-control',
                        ]) !!}
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
<!-- End of Sidebar -->
