<x-layouts.cpanel.billings.app-layout title="Receipts">

    <div class="card-header py-3">
        <h5 class="mb-2 font-weight-bold text-primary">{{ __('receipts.header.receipt_list') }}</h5>
        @if (isset($message))
            <div class="alert alert-danger mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ $message }}
            </div>
        @else
            <div class="alert alert-success mt-2">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                {{ __('receipts.message.total_receipts', ['count' => $receipts->total()]) }}
            </div>
        @endif
    </div>

    <div class="card-body">
        {{-- Pembungkus Utama Atas: Pagination di kiri, Semua Form/Tombol Filter di kanan --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mt-3">

            {{-- Pagination Kiri --}}
            <div>
                {{ $receipts->withQueryString()->links() }}
            </div>

            {{-- Kelompok Filter Kanan (Disatukan dalam inline flex Bootstrap 4) --}}
            <div class="d-flex align-items-center flex-wrap">

                {{-- === 📑 1. Form Filter Spesifik (Lokal Data via Select2) === --}}
                {!! Form::open([
                    'url' => url()->current(),
                    'method' => 'GET',
                    'class' => 'form-inline mr-2 my-1',
                    'id' => 'advancedFilterForm',
                ]) !!}

                {{-- Pertahankan parameter search/pagination lain --}}
                @foreach (request()->except(['filter_customer', 'filter_teller', 'filter_date', 'page']) as $key => $value)
                    @if (is_array($value))
                        @foreach ($value as $v)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="form-group mr-2 my-1" style="min-width: 200px;">
                    {!! Form::select('filter_customer', $customers, request('filter_customer'), [
                        'placeholder' => __('receipts.placeholder.select_customer_name'),
                        'id' => 'filterCustomer',
                        'class' => 'form-control form-control-sm select2-local w-100',
                    ]) !!}
                </div>

                <div class="form-group mr-2 my-1" style="min-width: 180px;">
                    {!! Form::select('filter_teller', $tellers, request('filter_teller'), [
                        'placeholder' => __('receipts.placeholder.select_teller'),
                        'id' => 'filterTeller',
                        'class' => 'form-control form-control-sm select2-local w-100',
                    ]) !!}
                </div>

                <div class="form-group mr-2 my-1">
                    <input type="text" name="filter_date" id="filterDate"
                        class="form-control form-control-sm datetimepicker"
                        placeholder="{{ __('receipts.placeholder.select_date') }}" value="{{ request('filter_date') }}"
                        autocomplete="off" style="width: 150px;">
                </div>

                <button type="submit" class="btn btn-primary btn-sm my-1 mr-1">
                    <i class="fas fa-search mr-1"></i> {{ __('receipts.button.filter') }}
                </button>

                @if (request('filter_customer') || request('filter_teller') || request('filter_date'))
                    <a href="{{ url()->current() }}"
                        class="btn btn-secondary btn-sm my-1">{{ __('receipts.button.reset') }}</a>
                @endif

                {!! Form::close() !!}

                {{-- === 📤 2. Fitur Kelompok Tombol Export === --}}
                @canany(['receipt-export-all', 'receipt-export-selected-period'])

                    @can('receipt-export-selected-period')
                        {{-- Export per bulan (Dibersihkan dari col-md agar tidak merusak baris) --}}
                        <form action="{{ route('receipts.export.selected') }}" method="GET" class="form-inline mr-2 my-1">
                            {!! Form::select('period', $periods, request('period'), [
                                'placeholder' => __('receipts.placeholder.periode'),
                                'class' => 'form-control form-control-sm',
                                'style' => 'width:auto;',
                                'onchange' => 'this.form.submit()',
                            ]) !!}
                        </form>
                    @endcan

                    @can('receipt-export-all')
                        {{-- Export semua --}}
                        <form action="{{ route('receipts.export.all') }}" method="GET" class="form-inline my-1">
                            <input type="hidden" name="all" value="1">
                            <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center"
                                style="height: 31px;">
                                <i class="fas fa-file-excel mr-2"></i>{{ __('receipts.button.export_all') }}
                            </button>
                        </form>
                    @endcan

                @endcanany
            </div>
        </div>

        @if ($totalPrice > 0)
            <div class="alert alert-success mt-2">
                <strong> {{ __('receipts.label.total_payments') }}: @moneyIDR($totalPrice) </strong>
            </div>
        @endif

        @if ($receipts->isNotEmpty())
            <div style="overflow-x: auto;" class="mt-3">
                <table id="billingsTable" cellspacing="0" width="100%"
                    class="table table-bordered table-striped table-hover">
                    <tr class="table-primary text-center">
                        <th>{{ __('receipts.table.no') }}</th>
                        <th>{{ __('receipts.table.customer_name') }}</th>
                        <th>{{ __('receipts.table.customer_id') }}</th>
                        <th>{{ __('receipts.table.address') }}</th>
                        <th>{{ __('receipts.table.periode') }}</th>
                        <th>{{ __('receipts.table.bill') }}</th>
                        <th>{{ __('receipts.table.payment_method') }}</th>
                        <th>{{ __('receipts.table.teller_name') }}</th>
                        <th>{{ __('receipts.table.actions') }}</th>
                    </tr>
                    @foreach ($receipts as $key => $data)
                        <tr>
                            <td><span class="badge text-primary">{{ ++$i }}</span></td>

                            <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span
                                        class="badge text-primary">{{ $data->customer_name ?? __('receipts.label.no_name') }}</span></a>
                            </td>
                            <td><a href="{{ route('receipts.show', $data->slug) }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Show Billing">
                                    <span
                                        class="badge text-primary">{{ $data->customer->customer_id ?? __('receipts.label.no_customer_id') }}</span></a>
                            </td>
                            <td>
                                <span class="badge text-primary"
                                    style="white-space: normal; text-align: left; display: inline-block; max-width: 220px; line-height: 1.4;">
                                    {{ $data->customer_address ?? __('receipts.label.no_address') }}
                                </span>
                            </td>
                            <td><span
                                    class="badge text-primary">{{ Carbon\Carbon::parse($data->periode)->translatedFormat('M Y') ?? __('receipts.label.no_periode') }}</span>
                            </td>
                            <td>
                                <span class="badge bg-blue-soft text-primary">
                                    @if ($data->package_price === 0)
                                        {{ __('receipts.label.free') }}
                                    @else
                                        @moneyIDR($data->package_price)
                                    @endif
                                </span>
                            </td>
                            <td>
                                @php
                                    $method = match ($data->payment_methode) {
                                        null => __('receipts.label.no_payment_method'),
                                        'cash' => __('receipts.label.cash'),
                                        'transfer' => __('receipts.label.transfer'),
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
                                        {{ __('receipts.label.on') }} {{ $formattedDate }}
                                    @else
                                        <div style="margin-top: 4px;">
                                            {{ __('receipts.label.on') }} {{ $formattedDate }}
                                        </div>
                                    @endif
                                </span>
                            </td>
                            <td><span
                                    class="badge text-primary">{{ $data->teller_name ?? __('receipts.label.no_teller') }}</span>
                            </td>
                            <td class="d-flex items-center justify-content-end">
                                @can('billing-edit')
                                    <a class="btn btn-success btn-sm" href="{{ route('receipts.print', $data->slug) }}"
                                        target="_blank"><i class="fa fa-print"></i> {{ __('receipts.button.print') }}</a>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @else
            <div class="alert alert-danger mt-2">
                {{ __('receipts.message.no_receipts') }}
            </div>
        @endif
        <div class="mt-3">
            {{ $receipts->withQueryString()->links() }}
        </div>
    </div>

    @section('custom_scripts')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

        <script>
            $(document).ready(function() {
                // Inisialisasi Select2 Standar untuk Data Lokal (Otomatis bisa search text)
                $('#filterCustomer').select2({
                    placeholder: '-- Pilih Pelanggan --',
                    allowClear: true,
                    width: 'resolve'
                });

                $('#filterTeller').select2({
                    placeholder: '-- Pilih Teller --',
                    allowClear: true,
                    width: 'resolve'
                });

                // Inisialisasi Datepicker
                $('#filterDate').datepicker({
                    format: 'yyyy-mm-dd',
                    autoclose: true,
                    todayHighlight: true
                });
            });
        </script>
    @endsection
</x-layouts.cpanel.billings.app-layout>
