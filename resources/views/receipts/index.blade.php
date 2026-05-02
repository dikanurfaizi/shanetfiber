<x-layouts.cpanel.billings.app-layout title="Receipts">

    <div class="card-header py-3">
        <h5 class="mb-2 font-weight-bold text-primary">{{ __('Receipts') }}</h5>
        @if (isset($message))
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
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mt-3">
            {{ $receipts->withQueryString()->links() }}

            @canany(['receipt-export-all', 'receipt-export-selected-period'])
                <div class="d-flex gap-2">
                    @can('receipt-export-selected-period')
                        {{-- Export per bulan --}}
                        <form action="{{ route('receipts.export.selected') }}" method="GET" class="d-flex align-items-center">
                            <div class="col-md-4 col-lg-3">
                                {!! Form::select('period', $periods, request('period'), [
                                    'placeholder' => __('billing.placeholder.search-period'),
                                    'class' => 'form-control form-select-sm me-2',
                                    'style' => 'width:auto;',
                                    'onchange' => 'this.form.submit()',
                                ]) !!}
                            </div>
                        </form>
                    @endcan

                    @can('receipt-export-all')
                        {{-- Export semua --}}
                        <form action="{{ route('receipts.export.all') }}" method="GET">
                            <input type="hidden" name="all" value="1">
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center"
                                style="height: 35px;">
                                <i class="fas fa-file-excel me-1 mr-2"></i>{{ __('billing.button.export-all-receipt') }}
                            </button>
                        </form>
                    @endcan
                </div>
            @endcanany
        </div>
        @if ($totalPrice > 0)
            <div class="alert alert-success mt-2">
                <strong> Total Payments: @moneyIDR($totalPrice) </strong>
            </div>
        @endif
        @if ($receipts->isNotEmpty())
            <div style="overflow-x: auto;">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <tr class="table-primary text-center">
                        <th>{{ __('billing.table.no') }}</th>
                        <th>{{ __('billing.table.customer-name') }}</th>
                        <th>{{ __('billing.table.customer_id') }}</th>
                        <th>{{ __('billing.table.address') }}</th>
                        <th>{{ __('billing.table.periode') }}</th>
                        <th>{{ __('billing.table.bill') }}</th>
                        <th>{{ __('billing.table.payment-methode') }}</th>
                        <th>{{ __('billing.table.teller-name') }}</th>
                        <th>{{ __('customer.table.action') }}</th>
                    </tr>
                    @foreach ($receipts as $key => $data)
                        <tr>
                            <td><span class="badge text-primary">{{ ++$i }}</span></td>

                            <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span class="badge text-primary">{{ $data->customer_name ?? 'no name' }}</span></a>
                            </td>
                            <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span
                                        class="badge text-primary">{{ $data->customer->customer_id ?? 'no customer ID' }}</span></a>
                            </td>
                            <td>
                                <span class="badge text-primary"
                                    style="white-space: normal; text-align: left; display: inline-block; max-width: 220px; line-height: 1.4;">
                                    {{ $data->customer_address ?? 'no address' }}
                                </span>
                            </td>
                            <td><span
                                    class="badge text-primary">{{ Carbon\Carbon::parse($data->periode)->translatedFormat('M Y') ?? 'no periode' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-blue-soft text-primary">
                                    @if ($data->package_price === 0)
                                        {{ __('Free') }}
                                    @else
                                        @moneyIDR($data->package_price)
                                    @endif
                                </span>
                            </td>
                            <td>
                                @php
                                    $method = match ($data->payment_methode) {
                                        null => __('No Payment Method'),
                                        'cash' => __('Cash'),
                                        'transfer' => 'Transfer',
                                        default => $data->payment_methode,
                                    };

                                    $isInline = in_array($data->payment_methode, ['cash', 'transfer']);
                                    $formattedDate = \Carbon\Carbon::parse($data->payment_time)->translatedFormat(
                                        'd M Y H:i \W\I\B',
                                    );
                                @endphp

                                <span class="badge bg-success text-white">
                                    <i class="fas fa-check-circle"></i>
                                    {{ $method }}

                                    @if ($isInline)
                                        on {{ $formattedDate }}
                                    @else
                                        <div style="margin-top: 4px;">
                                            on {{ $formattedDate }}
                                        </div>
                                    @endif
                                </span>
                            </td>
                            <td><span class="badge text-primary">{{ $data->teller_name ?? 'no name' }}</span></td>
                            <td class="d-flex items-center justify-content-end">
                                @can('billing-edit')
                                    <a class="btn btn-success btn-sm" href="{{ route('receipts.print', $data->slug) }}"
                                        target="_blank"><i class="fa fa-print"></i> Print PDF</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @else
            <div class="alert alert-danger mt-2">
                No receipt found!
            </div>
        @endif
        {{ $receipts->withQueryString()->links() }}
    </div>
</x-layouts.cpanel.billings.app-layout>
