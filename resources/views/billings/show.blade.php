<x-layouts.cpanel.billings.app-layout title="Billing Show">

    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">{{ __('Billing Detail: ') }}Customer Id:
            {{ $billing->customer->customer_id ?? 'no customer id' }}, Invocie Number:
            {{ $billing->billing_number ?? 'no number' }}</h6>
    </div>

    <div class="card-body col-lg-8">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Customer ID:</strong>
                <span class="badge bg-success text-white">
                    {{ $billing->customer->customer_id ?? 'no customer id' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Number:</strong>
                <span class="badge bg-success text-white">
                    {{ $billing->billing_number ?? 'no number' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Name:</strong>
                <a
                    href="{{ route('customers.show', $billing->customer->slug) }}">{{ $billing->customer_name ?? 'no data' }}</a>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Address:</strong>
                {{ $billing->customer->address ?? 'no data' }}
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Periode:</strong>
                {{ $billing->billingSubscription->billing_periode ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Package:</strong>
                {{ $billing->customer->package->name ?? 'no data' }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Package Price:</strong>
                @if ($billing->customer->package->price === 0)
                    {{ __('Free') }}
                @else
                    @moneyIDR($billing->customer->package->price)
                @endif
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Registered as:</strong>
                {{ $memberDisplay }}
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Discount:</strong>
                @moneyIDR($discount)
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Total Price:</strong>
                @if ($billing->total_amount === 0)
                    {{ __('Free') }}
                @else
                    @moneyIDR($billing->total_amount)
                @endif
            </div>
        </div>
        @if ($websystem->enable_tax === 'true')
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Tax {{ $websystem->tax_rate }}%:</strong>
                    @moneyIDR($billing->tax)
                </div>
            </div>

            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Total Bill:</strong>

                    @moneyIDR($billing->total_amount)
                </div>
            </div>
        @endif
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>User Created:</strong>
                <span class="badge bg-danger-soft text-danger">
                    <i class="fas fa-id-card-alt"></i> {{ $billing->created_name ?? 'no data' }}
                </span>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Status:</strong>
                @if ($billing->status === 'LS')
                    <span class="badge bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                        {{ __('billing.state.lunas') }}
                    </span>
                    {{ __('on ') }}
                    <span class="badge bg-success text-white">
                        {{ Carbon\Carbon::parse($billing->payment_time)->translatedFormat('l, d F Y H:i') }} WIB
                    </span>
                @elseif ($billing->status === 'PL')
                    <span class="badge bg-warning text-white">
                        <i class="fas fa-ban"></i>
                        {{ __('billing.state.paylater') }}
                    </span>
                    {{ __('on ') }}
                    @php
                        $otherDate = Carbon\Carbon::parse($billing->pay_later);
                        $nowDate = Carbon\Carbon::now();
                        $result = $nowDate->gt($otherDate);
                    @endphp
                    <span class="badge @if ($result) bg-danger @else bg-warning @endif text-white">
                        {{ Carbon\Carbon::parse($billing->pay_later)->translatedFormat('l, d F Y') }}
                    </span>
                @else
                    <span class="badge bg-danger text-white">
                        <i class="fas fa-ban"></i>
                        {{ __('billing.state.belum-lunas') }}
                    </span>
                @endif
            </div>
        </div>
        @if ($billing->status === 'LS' || $billing->status === 'PL')
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Teller:</strong>
                    <span class="badge bg-danger-soft text-danger">
                        <i class="fas fa-id-card-alt"></i> {{ $billing->teller_name ?? 'No User' }}
                    </span>
                </div>
            </div>
        @endif
        @if ($billing->status === 'LS' && $billing->total_amount > 0)
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Payment Methode:</strong>
                    <span class="badge bg-s-soft text-primary">
                        <i class="fas fa-id-card-alt"></i>
                        {{ $billing->receipt->payment_methode ?? 'No Payment Methode' }}
                        @if ($billing->receipt->payment_methode === 'transfer')
                            <i> - </i> {{ $billing->receipt->account_name ?? 'No Name' }} (
                            {{ $billing->receipt->bank_name ?? 'No Bank' }} -
                            {{ $billing->receipt->account_number ?? 'No Number' }} )
                        @endif
                    </span>
                </div>
            </div>
        @endif
        @if ($billing->total_amount > 0 && $billing->status === 'LS' && $billing->receipt->payment_methode === 'transfer')
            <div class="col-xs-12 col-sm-12 col-md-12">
                <div class="form-group">
                    <strong>Reference Id:</strong>
                    <span class="badge bg-s-soft text-primary">
                        <i class="fas fa-id-card-alt"></i>
                        {{ $billing->receipt->reference_id ?? 'No Payment Methode' }}
                    </span>
                </div>
            </div>
        @endif

    </div>
    <div class="card-footer">
        @can('billing-edit')
            @if ($billing->status === 'BL' || $billing->status === 'PL')
                {!! Form::model($billing, [
                    'method' => 'PATCH',
                    'route' => ['billings.payment', $billing->slug],
                    'style' => 'display:inline',
                ]) !!}
                <a href="#" class="btn btn-success btn-sm"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-money-bill-wave text-white"></i>
                    {{ __('billing.button.pay') }}
                </a>
                {!! Form::close() !!}
            @elseif ($billing->status === 'LS' && $billing->total_amount > 0)
                {!! Form::model($billing, [
                    'method' => 'PATCH',
                    'route' => ['billings.unpayment', $billing->slug],
                    'style' => 'display:inline',
                ]) !!}
                <a href="#" class="btn btn-danger btn-sm"
                    onclick="event.preventDefault(); this.closest('form').submit();">
                    <i class="fas fa-money-bill-wave text-white"></i>
                    {{ __('billing.button.unpay') }}
                </a>
                <a class="btn btn-sm btn-primary" href="{{ route('receipts.show', $billing->receipt->slug) }}"><i
                        class="fa fa-eye"></i> View Receipt</a>
            @endif
        @endcan
        <a class="btn btn-sm btn-secondary" href="{{ route('billings.index') }}"><i class="fa fa-arrow-left"></i>
            Back</a>
    </div>



    @if ($totalPrice > 0)
        <div class="card-header py-3">
            <h5 class="mb-2 font-weight-bold text-primary">{{ __('Tunggakan Tagihan') }} - Total: @moneyIDR($totalPrice)
            </h5>
        </div>

        <div class="card-body">
            <div style="overflow-x: auto;">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table  datatable-loading no-footer sortable searchable">
                    <tr>
                        <th>{{ __('billing.table.no') }}</th>
                        <th>{{ __('billing.table.periode') }}</th>
                        <th>{{ __('billing.table.package-name') }}</th>
                        <th>{{ __('billing.table.package-price') }}</th>
                        <th>{{ __('billing.table.bill-state') }}</th>
                        @can('billing-edit')
                            <th width="120px">{{ __('customer.table.action') }}</th>
                        @endcan
                    </tr>
                    @foreach ($billings as $key => $billing)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td><a href="{{ route('billings.show', $billing->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="Show Billing">{{ $billing->billingSubscription->billing_periode }}</a></td>
                            <td>
                                <span class="badge text-success">
                                    <i class="fas fa-chalkboard"></i> {{ $billing->customer->package->name }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-blue-soft text-primary">
                                    @if ($billing->base_price === 0)
                                        {{ __('Free') }}
                                    @else
                                        @moneyIDR($billing->base_price + $billing->tax)
                                    @endif

                                </span>
                            </td>
                            <td>
                                @if ($billing->status === 'LS')
                                    <span class="badge bg-success text-white">
                                        <i class="fas fa-check-circle"></i>
                                        {{ __('billing.state.lunas') }}
                                    </span>
                                @elseif ($billing->status === 'PL')
                                    <span class="badge bg-warning text-white">
                                        <i class="fas fa-ban"></i>
                                        {{ __('billing.state.paylater') }}
                                    </span>
                                @else
                                    <span class="badge bg-danger text-white">
                                        <i class="fas fa-ban"></i>
                                        {{ __('billing.state.belum-lunas') }}
                                    </span>
                                @endif
                            </td>
                            @can('billing-edit')
                                <td class="d-flex items-center justify-content-end">
                                    {!! Form::model($billing, ['method' => 'PATCH', 'route' => ['billings.payment', $billing->slug]]) !!}
                                    <a href="#" class="btn btn-success btn-sm"
                                        onclick="event.preventDefault(); this.closest('form').submit();">
                                        <i class="fas fa-money-bill-wave text-white"></i>
                                        {{ __('billing.button.pay') }}
                                    </a>
                                    {!! Form::close() !!}
                                </td>
                            @endcan
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    @endif
</x-layouts.cpanel.billings.app-layout>
