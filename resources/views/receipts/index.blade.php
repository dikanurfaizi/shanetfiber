<x-layouts.cpanel.billings.app-layout title="Receipts">

    <div class="card-header py-3">
        <h5 class="mb-2 font-weight-bold text-primary">{{ __('Receipts') }}</h5>
        @if(isset($message))
        <div class="alert alert-danger mt-2">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ $message }}
        </div>
        @else
        <div class="alert alert-success mt-2">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            {{ $receipts->total() }} receipts found
        </div>
        @endif
    </div>

    <div class="card-body">
        {{ $receipts->withQueryString()->links() }}
        @if ($totalPrice > 0)
        <div class="alert alert-success mt-2">
            <strong> Total Payments: @moneyIDR($totalPrice) </strong>
        </div>
        @endif
        @if($receipts->isNotEmpty())
        <table id="billingsTable" cellspacing="0" width="100%" class="table table-bordered table-striped table-hover">
            <tr class="table-primary text-center">
                <th>{{ __('billing.table.no') }}</th>
                <th>{{ __('billing.table.customer-name') }}</th>
                <th>{{ __('billing.table.customer_id') }}</th>
                <th>{{ __('billing.table.address') }}</th>
                <th>{{ __('billing.table.periode') }}</th>
                <th>{{ __('billing.table.bill') }}</th>
                <th>{{ __('billing.table.payment-methode') }}</th>
                <th>{{ __('customer.table.action') }}</th>
            </tr>
            @foreach ($receipts as $key => $data)
            <tr>
                <td><span class="badge text-primary">{{ ++$i }}</span></td>

                <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Show Billing">
                        <span class="badge text-primary">{{$data->customer_name ?? 'no name'}}</span></a>
                </td>
                <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip" data-bs-placement="top" title="Show Billing">
                        <span class="badge text-primary">{{$data->customer->customer_id ?? 'no customer ID'}}</span></a>
                </td>
                <td><span class="badge text-primary">{{ $data->customer_address ?? 'no address' }}</span></td>
                <td><span class="badge text-primary">{{ Carbon\Carbon::parse($data->periode)->translatedFormat('M Y') ?? 'no periode' }}</span></td>
                <td>
                    <span class="badge bg-blue-soft text-primary">
                        @if ($data->paket_price === 0)
                        {{ __('Free') }}
                        @else
                        @moneyIDR($data->paket_price)
                        @endif
                    </span>
                </td>
                <td>
                    <span class="badge bg-success text-white">
                        <i class="fas fa-check-circle"></i>
                        @if ($data->payment_methode === 'cash')
                        {{ __('Cash') }}
                        @elseif ($data->payment_methode === 'transfer')
                        {{'Transfer '}}
                        @else
                        {{ __('No Payment Methode') }}
                        @endif
                        on {{ Carbon\Carbon::parse($data->payment_time)->translatedFormat('d M Y') }}
                    </span>
                </td>

                <td class="d-flex items-center justify-content-end">
                    @can('billing-edit')
                    <a class="btn btn-success btn-sm" href="{{ route('receipts.print', $data->slug) }}" target="_blank"><i class="fa fa-print"></i> Print PDF</a>
                    @endcan
                </td>
            </tr>
            @endforeach
        </table>
        @else
        <div class="alert alert-danger mt-2">
            No receipt found!
        </div>
        @endif
        {{ $receipts->withQueryString()->links() }}
    </div>
</x-layouts.cpanel.billings.app-layout>