<x-layouts.cpanel.billings.app-layout title="Billings">

    <div class="card-header py-3">
        <h5 class="mb-2 font-weight-bold text-primary">{{ __('billing.header.index') }}</h5>
        @if (isset($message))
            <div class="alert alert-danger mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $message }}
            </div>
        @else
            <div class="alert alert-success mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $data->total() }} billings found.
            </div>
        @endif
        <span>
            <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseSearchCustomer"
                aria-expanded="false" aria-controls="collapseSearchCustomer" style="padding-top: inherit;">
                <i class="fas fa-search"></i>
                <span>{{ __('billing.header.search') }}</span>
            </a>
            <div class="collapse @if (request()->routeIs('billings.index')) show @endif" id="collapseSearchCustomer">
                <div class="card-header py-3 rounded">
                    <form>
                        <div class="row gx-3 gy-2">
                            <!-- Search Customer Name -->
                            <div class="col-md-4 col-lg-3">
                                {!! Form::text('search_name', null, [
                                    'placeholder' => __('billing.placeholder.search-customer-name'),
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <!-- Search Address -->
                            <div class="col-md-4 col-lg-3">
                                <select name="search_address" class="form-control">
                                    <option value="">{{ __('billing.placeholder.search-address') }}</option>
                                    <option value="no-address">{{ __('billing.placeholder.search-without-address') }}
                                    </option>
                                    @foreach ($address as $addrr)
                                        <option value="{{ $addrr }}">{{ $addrr }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Search Month -->
                            <div class="col-md-4 col-lg-2">
                                {!! Form::select('search_month', $months, null, [
                                    'placeholder' => __('billing.placeholder.search-month'),
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <!-- Search Year -->
                            <div class="col-md-4 col-lg-2">
                                {!! Form::select('search_year', $years, null, [
                                    'placeholder' => __('billing.placeholder.search-year'),
                                    'class' => 'form-control',
                                ]) !!}
                            </div>

                            <!-- Search Status -->
                            <div class="col-md-4 col-lg-2">
                                <select name="search_status" class="form-control">
                                    <option value="">{{ __('billing.placeholder.select.status') }}</option>
                                    <option value="!LS">{{ __('billing.placeholder.select.unpaid') }}</option>
                                    <option value="PL">{{ __('billing.placeholder.select.paylater') }}</option>
                                    <option value="LS">{{ __('billing.placeholder.select.paid') }}</option>
                                    {{-- <option value="NP">{{ __('billing.placeholder.select.notactive') }}</option> --}}
                                </select>
                            </div>
                        </div>

                        <!-- Admin and Due Date with margin top -->
                        @canany(['view-customers-widget-total'])
                            <div class="row gx-3 gy-2 mt-3">
                                <!-- Search Admin -->
                                <div class="col-md-4 col-lg-3">
                                    {!! Form::select('admin_name', $userAdmin, null, [
                                        'placeholder' => __('billing.placeholder.search-admin'),
                                        'class' => 'form-control',
                                    ]) !!}
                                </div>

                                <!-- Due Date -->
                                <div class="col-md-4 col-lg-3">
                                    <input id="select-date" type="datetime-local" class="form-control" name="duedate"
                                        placeholder="Due Date">
                                </div>
                            </div>
                        @endcanany

                        <!-- Additional Options -->
                        <div class="row gx-3 gy-2 mt-3">
                            <div class="col-lg-3 col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" value="true" type="checkbox" name="cetak_pdf"
                                        id="cetakPdfCheckbox">
                                    <label class="form-check-label" for="cetakPdfCheckbox">
                                        {{ __('Cetak PDF?') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Search Button -->
                        <div class="row mt-3">
                            <div class="col text-end">
                                <button class="btn btn-success btn-sm" type="submit">
                                    <i class="fas fa-search"></i> {{ __('customer.button.search') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </span>
    </div>

    <div class="card-body">
        {{ $data->withQueryString()->links() }}
        @if ($totalPrice > 0)
            <div class="alert alert-success mt-2">
                <strong> Total Bill: @moneyIDR($totalPrice) </strong>
            </div>
        @endif
        @if ($data->isNotEmpty())
            <!-- Table Wrapper for Horizontal Scrolling -->
            <div style="overflow-x: auto;">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table table-sm table-bordered table-striped table-hover">
                    <thead class="thead-dark">
                        <tr class="text-center align-middle">
                            <th class="text-center align-middle">{{ __('billing.table.no') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.customer-name') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.customer_id') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.address') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.activation') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.periode') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.bill') }}</th>
                            <th class="text-center align-middle">{{ __('billing.table.bill-state') }}</th>
                            <th class="text-center align-middle">{{ __('Payment Methode') }}</th>
                            <th class="text-center align-middle">{{ __('customer.table.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>

                        @foreach ($data as $key => $billing)
                            <tr>
                                <th class="text-center"><span class="badge text-primary">{{ ++$i }}</span>
                                </th>

                                <td><a href="{{ route('billings.show', $billing->slug) }}" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Show Billing">
                                        <span
                                            class="badge text-primary">{{ $billing->customer_name ?? 'no name' }}</span></a>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ $billing->customer->customer_id ?? 'no customer id' }}</span>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ $billing->customer->address ?? 'no address' }}</span>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ Carbon\Carbon::parse($billing->customer->activation_date)->format('d M Y') ?? 'inactive' }}</span>
                                </td>
                                <td><span
                                        class="badge text-primary">{{ Carbon\Carbon::parse($billing->billing_periode->name)->translatedFormat('M Y') ?? 'no periode' }}</span>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-blue-soft
                                            @if ($billing->status === 'LS') text-success
                                            @elseif ($billing->status === 'NP')
                                                bg-secondary text-white
                                            @else
                                                text-danger @endif
                                    ">
                                        @if ($billing->paket_price === 0)
                                            {{ __('Free') }}
                                        @else
                                            @moneyIDR($billing->paket_price + $billing->tax)
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $billingsUnpaid = App\Models\Billing\Billing::where(
                                            'customer_id',
                                            $billing->customer_id,
                                        )
                                            ->where('status', '!=', 'LS')
                                            ->get();
                                    @endphp
                                    @if ($billing->status === 'LS')
                                        <span class="badge bg-success text-white">
                                            <i class="fas fa-check-circle"></i>
                                            {{ __('billing.state.lunas') }}
                                        </span>
                                    @elseif ($billing->status === 'PL')
                                        @php
                                            $otherDate = Carbon\Carbon::parse($billing->pay_later);
                                            $nowDate = Carbon\Carbon::now();
                                            $result = $nowDate->gt($otherDate);
                                        @endphp
                                        <span
                                            class="badge @if ($result) bg-danger @else bg-warning @endif text-white">
                                            <i class="fas fa-ban"></i>
                                            {{ __('billing.state.paylater') . ' ' . Carbon\Carbon::parse($billing->pay_later)->translatedFormat('d M Y') }}
                                        </span>
                                    @elseif ($billing->status === 'NP')
                                        <span class="badge bg-secondary text-white">
                                            <i class="fas fa-info-circle"></i>
                                            {{ __('billing.state.tidak-ada-pemakaian') }}
                                        </span>
                                    @else
                                        @php
                                            $activationDate =
                                                Carbon\Carbon::now()->translatedFormat('Y-m') .
                                                '-' .
                                                Carbon\Carbon::parse(
                                                    $billing->customer->activation_date,
                                                )->translatedFormat('d') .
                                                ' ' .
                                                Carbon\Carbon::now()->translatedFormat('H:i:s');
                                            $nowDate = Carbon\Carbon::now();
                                            $result = $nowDate->gt($activationDate);
                                        @endphp
                                        <span
                                            class="badge @if ($result) bg-danger @else bg-primary @endif text-white">
                                            <i class="fas fa-ban"></i>
                                            {{ __('billing.state.belum-lunas') }}
                                            {{ Carbon\Carbon::parse($billing->due_date)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                    @php
                                        $countBillingCustomerUnpaid = count(
                                            $data
                                                ->where('customer_id', $billing->customer_id)
                                                ->where('status', '!=', 'LS'),
                                        );
                                    @endphp
                                    @if (count($billingsUnpaid) - $countBillingCustomerUnpaid > 0)
                                        </br>
                                        <a class="text-xs fw-bold text-danger d-inline-flex align-items-center"
                                            href="{{ route('billings.index', ['search_status' => '!LS', 'customer' => $billing->customer->slug]) }}">
                                            {{ __('billing.state.belum-lunas-lainnya', ['count_bill_unpaid' => count($billingsUnpaid) - $countBillingCustomerUnpaid]) }}
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    @if ($billing->status === 'NP')
                                        <span class="badge bg-secondary text-white">
                                            <i class="fas fa-times-circle"></i>
                                            {{ __('Updated') }} on
                                            {{ Carbon\Carbon::parse($billing->updated_at)->translatedFormat('M Y') }}
                                        </span>
                                    @elseif ($billing->status != 'LS' && $billing->paket_price > 0)
                                        {!! Form::model($billing, [
                                            'method' => 'PATCH',
                                            'route' => ['billings.payment', $billing->slug],
                                            'id' => 'form-pay-' . $billing->slug,
                                        ]) !!}
                                        <select name="payment_methode" class="form-control"
                                            style="width:auto; display:inline-block;">
                                            <option value="cash">Cash</option>
                                            @if (count($bank))
                                                <option value="transfer">Transfer</option>
                                            @endif
                                            <option value="paylater">Pay Later</option>
                                            <option value="not-active-periode">Not Active Period</option>
                                        </select>
                                        {!! Form::close() !!}
                                    @elseif ($billing->status === 'LS' && $billing->paket_price > 0)
                                        <span class="badge bg-success text-white">
                                            <i class="fas fa-check-circle"></i>
                                            {{ $billing->receipt->payment_methode ?? '' }} on
                                            {{ Carbon\Carbon::parse($billing->payment_time)->translatedFormat('d M Y') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="items-center justify-content-end text-center">
                                    @can('billing-edit')
                                        @if ($billing->status === 'NP')
                                            <span class="badge bg-secondary text-white">
                                                <i class="fas fa-info-circle"></i>
                                                {{ __('Marked') }}
                                            </span>
                                            {{-- Tombol revert status jika ingin ubah kembali jadi unpaid --}}
                                            {{--
                                            {!! Form::model($billing, ['method' => 'PATCH', 'route' => ['billings.resetStatus', $billing->slug]]) !!}
                                                <a class="btn btn-warning btn-sm"
                                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                                    <i class="fas fa-undo text-white"></i>
                                                    {{ __('billing.button.reset_status') }}
                                                </a>
                                            {!! Form::close() !!}
                                            --}}
                                        @elseif ($billing->status != 'LS')
                                            @if ($billing->customer->activation === 'true')
                                                <a class="btn btn-success btn-sm"
                                                    onclick="document.getElementById('form-pay-{{ $billing->slug }}').submit();">
                                                    <i class="fas fa-money-bill-wave text-white"></i>
                                                    {{ __('billing.button.pay') }}
                                                </a>
                                            @else
                                                <form method="POST" class="d-inline"
                                                    action="{{ route('customers.activation', $billing->customer->slug) }}">
                                                    @csrf
                                                    @method('put')
                                                    <a class="btn btn-success btn-sm"
                                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                                        <i class="fa fa-check me-2 text-white"></i>
                                                        {{ __('billing.button.activation') }}
                                                    </a>
                                                </form>
                                            @endif
                                        @elseif ($billing->status === 'LS' && $billing->paket_price > 0)
                                            {!! Form::model($billing, ['method' => 'PATCH', 'route' => ['billings.unpayment', $billing->slug]]) !!}
                                            <a class="btn btn-danger btn-sm"
                                                onclick="event.preventDefault(); this.closest('form').submit();">
                                                <i class="fas fa-money-bill-wave text-white"></i>
                                                {{ __('billing.button.unpay') }}
                                            </a>
                                            {!! Form::close() !!}
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-danger mt-2">
                No billing found!
            </div>
        @endif
        {{ $data->withQueryString()->links() }}
    </div>
    @section('custom_styles')
        <link href="{{ asset('css/flatpickr.min.css') }}" rel="stylesheet">
    @endsection
</x-layouts.cpanel.billings.app-layout>
