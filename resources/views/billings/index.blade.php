<x-layouts.cpanel.billings.app-layout title="Billings">

    <div class="card-header py-3">
        <h5 class="mb-2 font-weight-bold text-primary">{{ __('billing.header.index') }}</h5>
        @if(isset($message))
        <div class="alert alert-danger mt-2">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ $message }}
        </div>
        @else
        <div class="alert alert-success mt-2">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ $data->total() }} billings found.
            <span class="badge bg-primary text-white">Name: {{$search['name'] ?? 'All'}}</span>
            <span class="badge bg-primary text-white">Address: {{$search['address'] ?? 'All'}}</span>
            <span class="badge bg-primary text-white">Status: {{$search['status'] ?? 'All'}}</span>
            <span class="badge bg-primary text-white">Bulan: {{$search['month'] ?? 'All'}}</span>
            <span class="badge bg-primary text-white">Tahun: {{$search['year'] ?? 'All'}}</span>
        </div>
        @endif
    </div>

    <div class="card-body">
        {{ $data->withQueryString()->links() }}
        @if ($totalPrice > 0)
        <div class="alert alert-success mt-2">
            <strong> Total Bill: @moneyIDR($totalPrice) </strong>
        </div>
        @endif
        @if($data->isNotEmpty())
        <table id="billingsTable" cellspacing="0" width="100%" class="table table-sm table-bordered table-striped table-hover">
            <thead class="thead-dark text-center">
                <tr>
                    <th>{{ __('billing.table.no') }}</th>
                    <th>{{ __('billing.table.customer-name') }}</th>
                    <th>{{ __('billing.table.customer_id') }}</th>
                    <th>{{ __('billing.table.address') }}</th>
                    <th>{{ __('billing.table.periode') }}</th>
                    <th>{{ __('billing.table.bill') }}</th>
                    <th>{{ __('billing.table.bill-state') }}</th>
                    <th>{{ __('Payment Methode') }}</th>
                    <th>{{ __('customer.table.action') }}</th>
                </tr>
            </thead>
            <tbody>

                @foreach ($data as $key => $billing)


                <tr>
                    <th class="text-center"><span class="badge text-primary">{{ ++$i }}</span></th>

                    <td><a href="{{ route('billings.show', $billing->slug) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Show Billing">
                            <span class="badge text-primary">{{$billing->customer_name ?? 'no name'}}</span></a>
                    </td>
                    <td><span class="badge text-primary">{{ $billing->customer->customer_id ?? 'no customer id' }}</span></td>
                    <td><span class="badge text-primary">{{ $billing->customer->address ?? 'no address' }}</span></td>
                    <td><span class="badge text-primary">{{ Carbon\Carbon::parse($billing->billing_periode->name)->translatedFormat('M Y') ?? 'no periode' }}</span></td>
                    <td>
                        <span class="badge bg-blue-soft @if ($billing->status === 'LS') text-success @else text-danger @endif">
                            @if ($billing->paket_price === 0)
                            {{ __('Free') }}
                            @else
                            @moneyIDR($billing->paket_price + $billing->tax)
                            @endif
                        </span>
                    </td>
                    <td>
                        @php
                        $billingsUnpaid = App\Models\Billing\Billing::where('customer_id', $billing->customer_id)->where('status', '!=', 'LS')->get();
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
                        <span class="badge  @if ($result) bg-danger @else bg-warning @endif text-white">
                            <i class="fas fa-ban"></i>
                            {{ __('billing.state.paylater').' '.Carbon\Carbon::parse($billing->pay_later)->translatedFormat('d M Y') }}
                        </span>
                        @else
                        @php
                        $activationDate = Carbon\Carbon::now()->translatedFormat('Y-m').'-'.Carbon\Carbon::parse($billing->customer->activation_date)->translatedFormat('d'). ' '.Carbon\Carbon::now()->translatedFormat('H:i:s');
                        $nowDate = Carbon\Carbon::now();
                        $result = $nowDate->gt($activationDate);
                        @endphp
                        <span class="badge @if ($result) bg-danger @else bg-primary @endif text-white">
                            <i class="fas fa-ban"></i>
                            {{ __('billing.state.belum-lunas') }} {{ Carbon\Carbon::parse($billing->due_date)->translatedFormat('d M Y') }}
                        </span>
                        @endif
                        @php
                        $countBillingCustomerUnpaid = count($data->where('customer_id', $billing->customer_id)->where('status', '!=', 'LS'));
                        @endphp
                        @if (count($billingsUnpaid) - $countBillingCustomerUnpaid > 0)
                        </br>
                        <a class="text-xs fw-bold text-danger d-inline-flex align-items-center" href="{{ route('billings.index', ['search_status' => '!LS', 'customer' => $billing->customer->slug]) }}">
                            {{ __('billing.state.belum-lunas-lainnya', ['count_bill_unpaid'=> count($billingsUnpaid) - $countBillingCustomerUnpaid]) }}
                        </a>
                        @endif
                    </td>
                    <td>
                        @if ($billing->status != 'LS' && $billing->paket_price > 0)
                        {!! Form::model($billing, ['method' => 'PATCH','route' => ['billings.payment', $billing->slug], 'id' => 'form-pay-'.$billing->slug]) !!}
                        <select name="payment_methode" class="form-control" style="width:auto; display:inline-block;">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="paylater">Pay Later</option>
                        </select>
                        {!! Form::close() !!}

                        @elseif ($billing->status === 'LS' && $billing->paket_price > 0)
                        <span class="badge bg-success text-white">
                            <i class="fas fa-check-circle"></i>
                            {{ $billing->receipt->payment_methode ?? '' }} on {{ Carbon\Carbon::parse($billing->payment_time)->translatedFormat('d M Y') }}
                        </span>
                        @endif
                    </td>

                    <td class="items-center justify-content-end text-center">
                        @can('billing-edit')
                        @if ($billing->status != 'LS')
                        @if ($billing->customer->activation === 'true')

                        <a class="btn btn-success btn-sm" onclick="document.getElementById('form-pay-{{ $billing->slug }}').submit();">
                            <i class="fas fa-money-bill-wave text-white"></i>
                            {{ __('billing.button.pay') }}
                        </a>

                        @else
                        <form method="POST" class="d-inline" action="{{ route('customers.activation',$billing->customer->slug) }}">
                            @csrf
                            @method('put')
                            <a class="btn btn-success btn-sm" onclick="event.preventDefault(); this.closest('form').submit();">
                                <i class="fa fa-check me-2 text-white"></i>
                                {{ __('billing.button.activation') }}
                            </a>
                        </form>
                        @endif
                        @elseif ($billing->status === 'LS' && $billing->paket_price > 0)
                        {!! Form::model($billing, ['method' => 'PATCH','route' => ['billings.unpayment', $billing->slug]]) !!}
                        <a class="btn btn-danger btn-sm" onclick="event.preventDefault(); this.closest('form').submit();">
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